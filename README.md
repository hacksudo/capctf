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
docker pull hacksudo/capctf:latest

# Run the container (maps port 8080)
docker run --rm -it -p 8080:80 hacksudo/capctf:latest
```

The lab will be accessible at http://localhost:8081/login.php

CAPCTF/
├─ Dockerfile           # Docker image build instructions
├─ entrypoint.sh        # Container startup script
├─ nginx.conf           # Nginx configuration
├─ init_db.php          # SQLite database initialization
├─ login.php            # Vulnerable login page
├─ dashboard.php        # Post-login admin dashboard
├─ ip_block.json        # Tracks IP login attempts
└─ README.md            # This documentation
