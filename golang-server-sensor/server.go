package main

import (
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"
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
	NODE_ID        int64
	PARENT_NODE_ID int64
}

var config Config
var nodes []Node

func main() {

	setupConfig()

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

	handlePing()
	buildNodeRelationship()
	// serves the node list
	serveNodeList()

	fmt.Printf("Server starting on port %s\n", config.PORT)
	if err := http.ListenAndServe(config.PORT, nil); err != nil {
		log.Fatalf("Server failed to start: %v", err)
	}
}

func serveNodeList() {
	http.HandleFunc("/node_collection", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(nodes)
		//fmt.Println(nodes)
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

		fmt.Fprintf(w, "ID: %d | Location: %s", ping.ID, ping.Location)
	})
}

func buildNodeRelationship() {

	resp, err := http.Get(config.ENDPOINT + "/api/node_relations")
	if err != nil {
		fmt.Println("Error occurred: " + err.Error())
		return
	}
	defer resp.Body.Close()
	fmt.Println(resp.Body)

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
