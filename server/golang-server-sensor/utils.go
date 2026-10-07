package main

import (
	"fmt"
	"os"

	"github.com/joho/godotenv"
)

func getServerConfig() (string, string) {
	if err := godotenv.Load(); err != nil {
		fmt.Println("Failed to load enviroment variables" + err.Error())
		return os.DevNull, os.DevNull
	}

	return os.Getenv("SERVER_PORT"), os.Getenv("LARAVEL_URL")
}
