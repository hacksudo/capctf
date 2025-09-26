# CAPCTF

**CAPCTF** is an isolated, hands-on web lab designed for safe CTF practice and web security learning. Users can explore the interface, manage sessions, and interact with the environment for educational purposes.

---

## Features

- Web-based login and dashboard
- Session and IP tracking
- Interactive practice environment
- Docker-ready for easy deployment

---

## Requirements

- Docker (latest version recommended)

---

## Setup Instructions

### Pull and Run Docker Image

```bash```
# Pull the latest image from Docker Hub
docker pull hacksudo/capctf:latest

# Run the container (maps port 8081)
docker run --rm -it -p 8080:80 hacksudo/capctf:latest
# Clone the repository
git clone https://github.com/hacksudo/CAPCTF.git
cd CAPCTF

# Build Docker image
docker build -t hacksudo/capctf:latest .

# Run container
docker run --rm -it -p 8080:80 hacksudo/capctf:latest



Do you want me to do that?
