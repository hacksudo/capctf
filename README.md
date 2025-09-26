# CAPCTF

**CAPCTF** is an interactive, hands-on web lab designed for practicing web application security and CTF challenges in a safe, isolated environment. This lab allows users to explore login flows, session management, and dashboard interactions while learning security concepts and testing in a controlled setup.

---

## Features

- Web-based login page with CAPTCHA
- Session and IP tracking
- Dashboard for post-login actions
- Educational practice environment
- Vulnerable by design (for learning)
- Fully containerized using Docker for easy deployment
- Pre-configured SQLite database for session and user tracking

---

## Requirements

- Docker (latest version recommended)
- Git (optional, for cloning the repo)

---

## Getting Started

### Option 1: Pull and Run Docker Image

```bash
# Pull the latest CAPCTF image from Docker Hub
docker pull <your-dockerhub-username>/capctf:latest

# Run the container (maps port 8081)
docker run --rm -it -p 8081:80 <your-dockerhub-username>/capctf:latest
