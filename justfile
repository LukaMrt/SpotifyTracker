# Root justfile for SpotifyTracker project
set shell := ["powershell.exe", "-Command"]

# Forward backend commands to backend directory
backend *args:
    cd backend; just {{args}}

# Show available backend commands
backend-help:
    cd backend; just --list

help:
    echo "Available commands:"
    echo "  backend [command] : Run backend commands"

default: help
