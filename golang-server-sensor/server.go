package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"
	"sync"
	"time"
)

type Node struct {
	ID        int64   `json:"id"`
	Longitude float64 `json:"longitude"`
	Latitude  float64 `json:"latitude"`
	Status    string  `json:"status"`
}

type NodePing struct {
	ID int64 `json:"id"`
}

type Config struct {
	PORT     string
	ENDPOINT string
}

type NodeRelation struct {
	ID           int64 `json:"id"`
	NodeID       int64 `json:"node_id"`
	ParentNodeID int64 `json:"parent_node_id"`
}

var (
	pingTracker      = make(map[int64]time.Time)
	pingTrackerMutex sync.Mutex

	dataMutex     sync.RWMutex
	nodes         []Node
	nodeRelations []NodeRelation

	config Config
)

func main() {
	if !setupConfig() {
		log.Fatal("Could not load complete environment configuration")
	}

	buildNodeList()
	buildNodeRelationship()

	go monitorNodeStatus()

	serveNodeList()
	serveNodeRelationshipList()

	handlePing()
	handleUpdateStatus()

	fmt.Printf("Server starting on port %s\n", config.PORT)
	if err := http.ListenAndServe(config.PORT, nil); err != nil {
		log.Fatalf("Server failed to start: %v", err)
	}
}

func serveNodeList() {
	http.HandleFunc("/node_collection", func(w http.ResponseWriter, r *http.Request) {
		buildNodeList()

		dataMutex.RLock()
		defer dataMutex.RUnlock()

		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(nodes)
		fmt.Println("Requested Nodes")
	})
}

func serveNodeRelationshipList() {
	http.HandleFunc("/node_relation_collection", func(w http.ResponseWriter, r *http.Request) {
		buildNodeRelationship()

		dataMutex.RLock()
		defer dataMutex.RUnlock()

		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(nodeRelations)
	})
}

func handlePing() {
	http.HandleFunc("/ping", func(w http.ResponseWriter, r *http.Request) {
		if r.Method != http.MethodPost {
			http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
			return
		}

		var ping NodePing
		if err := json.NewDecoder(r.Body).Decode(&ping); err != nil {
			http.Error(w, err.Error(), http.StatusBadRequest)
			return
		}

		pingTrackerMutex.Lock()
		_, exists := pingTracker[ping.ID]
		if !exists {
			go updateLaravelNodeStatus(ping.ID, "active")
		}
		pingTracker[ping.ID] = time.Now()
		pingTrackerMutex.Unlock()

		w.WriteHeader(http.StatusOK)
		fmt.Fprintf(w, "Ping acknowledged for node %d", ping.ID)
	})
}

func handleUpdateStatus() {
	http.HandleFunc("/update_status", func(w http.ResponseWriter, r *http.Request) {
		if r.Method != http.MethodPost {
			http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
			return
		}

		var req struct {
			ID     int64  `json:"id"`
			Status string `json:"status"`
		}
		if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
			http.Error(w, err.Error(), http.StatusBadRequest)
			return
		}

		pingTrackerMutex.Lock()
		if req.Status == "inactive" {
			delete(pingTracker, req.ID)
		} else if req.Status == "active" {
			pingTracker[req.ID] = time.Now()
		}
		pingTrackerMutex.Unlock()

		dataMutex.Lock()
		for i := range nodes {
			if nodes[i].ID == req.ID {
				nodes[i].Status = req.Status
				break
			}
		}
		dataMutex.Unlock()

		updateLaravelNodeStatus(req.ID, req.Status)

		w.WriteHeader(http.StatusOK)
		fmt.Fprintf(w, "Status for node %d updated to %s", req.ID, req.Status)
	})
}

func buildNodeRelationship() {
	resp, err := http.Get(config.ENDPOINT + "/api/node_relations")
	if err != nil {
		fmt.Printf("Error fetching relations: %v\n", err)
		return
	}
	defer resp.Body.Close()

	var fetchedRelations []NodeRelation
	if err := json.NewDecoder(resp.Body).Decode(&fetchedRelations); err != nil {
		fmt.Printf("Error decoding relations JSON: %v\n", err)
		return
	}

	dataMutex.Lock()
	nodeRelations = fetchedRelations
	dataMutex.Unlock()
}

func buildNodeList() {
	resp, err := http.Get(config.ENDPOINT + "/api/nodes")
	if err != nil {
		fmt.Printf("Error fetching nodes: %v\n", err)
		return
	}
	defer resp.Body.Close()

	var fetchedNodes []Node
	if err := json.NewDecoder(resp.Body).Decode(&fetchedNodes); err != nil {
		fmt.Printf("Error decoding nodes JSON: %v\n", err)
		return
	}

	dataMutex.Lock()
	nodes = fetchedNodes
	dataMutex.Unlock()
}

func monitorNodeStatus() {
	ticker := time.NewTicker(5 * time.Second)
	defer ticker.Stop()

	for range ticker.C {
		pingTrackerMutex.Lock()
		now := time.Now()

		for nodeID, lastPing := range pingTracker {
			// Mark node dead after 10 seconds without ping
			if now.Sub(lastPing) > 10*time.Second {
				fmt.Printf("ALERT: Node %d stopped pinging! Updating Laravel to inactive...\n", nodeID)

				go updateLaravelNodeStatus(nodeID, "inactive")
				delete(pingTracker, nodeID)
			}
		}
		pingTrackerMutex.Unlock()
	}
}

func updateLaravelNodeStatus(nodeID int64, status string) {
	node := getNode(nodeID)

	payload, err := json.Marshal(map[string]interface{}{
		"status":    status,
		"longitude": node.Longitude,
		"latitude":  node.Latitude,
	})
	if err != nil {
		fmt.Printf("Failed to marshal update payload for node %d: %v\n", nodeID, err)
		return
	}

	url := fmt.Sprintf("%s/api/node/update/%d", config.ENDPOINT, nodeID)
	req, err := http.NewRequest(http.MethodPut, url, bytes.NewBuffer(payload))
	if err != nil {
		fmt.Printf("Failed to create HTTP request for node %d: %v\n", nodeID, err)
		return
	}

	req.Header.Set("Content-Type", "application/json")
	client := &http.Client{Timeout: 5 * time.Second}

	resp, err := client.Do(req)
	if err != nil {
		fmt.Printf("Failed to notify Laravel for node %d: %v\n", nodeID, err)
		return
	}
	defer resp.Body.Close()

	fmt.Printf("Updated Laravel -> Node: %d | Status: %s (HTTP %d)\n", nodeID, status, resp.StatusCode)
}

func getNode(nodeID int64) Node {
	dataMutex.RLock()
	defer dataMutex.RUnlock()

	for _, node := range nodes {
		if node.ID == nodeID {
			return node
		}
	}
	return Node{ID: nodeID}
}

func setupConfig() bool {
	port, endPoint := getServerConfig()
	if port == os.DevNull || endPoint == os.DevNull || port == "" || endPoint == "" {
		fmt.Println("Couldn't load complete environment variables!")
		return false
	}
	config.ENDPOINT = endPoint
	config.PORT = port
	return true
}
