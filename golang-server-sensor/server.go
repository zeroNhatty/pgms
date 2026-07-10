package main

import (
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"

	"github.com/joho/godotenv"
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

func main() {

	// enviroment variables loading
	if err := godotenv.Load(); err != nil {
		fmt.Println("Failed to load enviroment variables" + err.Error())
		return
	}

	port := os.Getenv("SERVER_PORT")

	endPoint := os.Getenv("LARAVEL_URL")

	resp, err := http.Get(endPoint + "/api/nodes")
	if err != nil {
		fmt.Println("Error occurred: " + err.Error())
		return
	}
	defer resp.Body.Close()

	var nodes []Node

	err = json.NewDecoder(resp.Body).Decode(&nodes)
	if err != nil {
		fmt.Println("Error decoding JSON: " + err.Error())
		return
	}

	for _, node := range nodes {
		jsonIfied := fmt.Sprintf("id: %d | location: %s | status: %s", node.ID, node.Location, node.Status)
		fmt.Println(jsonIfied)
	}

	http.HandleFunc("/decode", func(w http.ResponseWriter, r *http.Request) {
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

	http.HandleFunc("/node_collection", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(nodes)
		fmt.Println(nodes)
	})

	fmt.Printf("Server starting on port %s\n", port)
	if err := http.ListenAndServe(port, nil); err != nil {
		log.Fatalf("Server failed to start: %v", err)
	}
}
