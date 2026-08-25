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
	ID       int64  `json:"id"`
	Location string `json:"location"`
	Status   string `json:"status"`
}

type NodePing struct {
	ID       int64  `json:"id"`
	Location string `json:"location"`
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
)

var (
	config        Config
	nodes         []Node
	nodeRelations []NodeRelation
)

func main() {
	setupConfig()

	buildNodeList()
	buildNodeRelationship()

	go monitorNodeStatus()
	// serves the node / relationship list
	serveNodeList()
	serveNodeRelationshipList()

	handlePing()

	fmt.Printf("Server starting on port %s\n", config.PORT)
	if err := http.ListenAndServe(config.PORT, nil); err != nil {
		log.Fatalf("Server failed to start: %v", err)
	}
}

func serveNodeList() {
	http.HandleFunc("/node_collection", func(w http.ResponseWriter, r *http.Request) {
		buildNodeList()
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(nodes)
		fmt.Println("Requested Nodes")
	})
}

func serveNodeRelationshipList() {
	http.HandleFunc("/node_relation_collection", func(w http.ResponseWriter, r *http.Request) {
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
		err := json.NewDecoder(r.Body).Decode(&ping)
		if err != nil {
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

func buildNodeRelationship() {
	resp, err := http.Get(config.ENDPOINT + "/api/node_relations")
	if err != nil {
		fmt.Println("Error occurred: " + err.Error())
		return
	}
	defer resp.Body.Close()

	err = json.NewDecoder(resp.Body).Decode(&nodeRelations)
	if err != nil {
		fmt.Println("Error decoding JSON payload: " + err.Error())
		return
	}

	/*for _, rel := range relations {
		fmt.Printf("Relation ID: %d | Node: %d is child of Parent: %d\n",
			rel.ID, rel.NodeID, rel.ParentNodeID)
	}*/
}

func monitorNodeStatus() {
	ticker := time.NewTicker(5 * time.Second)
	defer ticker.Stop()

	for range ticker.C {
		pingTrackerMutex.Lock()
		now := time.Now()

		for nodeID, lastPing := range pingTracker {
			// assumes node is dead after 10 sec
			if now.Sub(lastPing) > 10*time.Second {
				fmt.Printf("ALERT: Node %d has stopped pinging! Updating Laravel...\n", nodeID)

				// Trigger the Laravel update asynchronously so it doesn't stall this checker
				go updateLaravelNodeStatus(nodeID, "inactive")

				// kills a parents children
				/*
					for _, relation := range nodeRelations {
					if relation.ParentNodeID == nodeID {
						go updateLaravelNodeStatus(relation.NodeID, "inactive")
					}
					}
				*/

				delete(pingTracker, nodeID)
			}
		}
		pingTrackerMutex.Unlock()
	}
}

func updateLaravelNodeStatus(nodeID int64, status string) {
	node := getNode(nodeID)
	payload, _ := json.Marshal(map[string]interface{}{
		"id":       node.ID,
		"location": node.Location,
		"status":   status,
	})

	url := fmt.Sprintf("%s/api/node/update/%d", config.ENDPOINT, nodeID)
	req, _ := http.NewRequest(http.MethodPut, url, bytes.NewBuffer(payload))

	req.Header.Set("Content-Type", "application/json")
	client := &http.Client{}
	resp, err := client.Do(req)
	if err != nil {
		fmt.Printf("Failed to notify Laravel for node %d: %v\n", nodeID, err)
	}
	defer resp.Body.Close()
	fmt.Println("Update Laravel : ", nodeID, " | Status : ", status)
}

func buildNodeList() {
	resp, err := http.Get(config.ENDPOINT + "/api/nodes")
	if err != nil {
		fmt.Println("Error occurred: " + err.Error())
		return
	}
	defer resp.Body.Close()

	err = json.NewDecoder(resp.Body).Decode(&nodes)
	if err != nil {
		fmt.Println("Error decoding JSON: " + err.Error())
		return
	}
}

func setupConfig() bool {
	port, endPoint := getServerConfig()
	if port == os.DevNull || endPoint == os.DevNull {
		fmt.Println("Couldn't load complete enviroment variables!")
		return false
	}
	config.ENDPOINT = endPoint
	config.PORT = port
	return true
}

func getNode(nodeID int64) Node {
	for i, node := range nodes {
		if node.ID == nodeID {
			return nodes[i]
		}
	}
	return Node{}
}
