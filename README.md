# EasyVault.KRD 🔐

A secure, containerized password vault application built with **PHP**, **Docker**, and **MySQL**, designed with strong security principles and DevSecOps practices in mind.
Website also has features like checking weather your email has been in a recent databreach through API's
Also the website checks if your password is strong, also checks if your password is in any wordlists
---

## 📌 Project Overview

EasyVault.KRD is a web-based password manager that allows users to securely store and manage credentials. The system emphasizes **secure authentication**, **encryption**, **role-based access control**, and **safe deployment practices**.


---

## ✨ Key Features

### 👤 User Features

* Secure user registration and login
* Email verification using OTP
* Encrypted password vault (AES-256-GCM)
* Add, edit, view, and delete stored credentials
* Password visibility toggle (Show/Hide)

### 🛡️ Security Tools

* **Email Breach Checker** using Have I Been Pwned API
* **Password Strength Analyzer** (length, complexity, entropy)
* **Password Breach Check** using HIBP k-anonymity model (no password exposure)

### 👑 Admin Features

* Admin dashboard
* View and manage users
* View audit logs
* Role-based access control (admin vs user)

---

## 🔐 Security Design

Security was a primary design goal throughout the project:

* **Password Hashing**: Argon2id (industry standard)
* **Vault Encryption**: AES-256-GCM with per-session derived key
* **Key Derivation**: PBKDF2 (SHA-256, 100,000 iterations)
* **Session Protection**: Session regeneration on login
* **Rate Limiting**: Applied to sensitive actions (email checks)
* **Secrets Management**: All secrets stored in environment variables
* **No Plaintext Password Exposure**: HIBP k-anonymity model used

---

## 🏗️ Architecture Overview

**Tech Stack:**

* Frontend: HTML, CSS
* Backend: PHP 8
* Database: MySQL 8
* Containerization: Docker & Docker Compose
* Deployment: Railway

```
Browser
   ↓
PHP (Apache)
   ↓
MySQL Database
```

Sensitive operations (authentication, encryption, email, API calls) are isolated into dedicated libraries.

---

## 🐳 Docker & Deployment

The application is fully containerized and runs consistently across environments.

### Docker Components

* PHP-Apache container
* MySQL container

### Deployment

* Hosted on **Railway**
* Environment variables configured securely via Railway dashboard

---

## 🔁 CI/CD Pipeline

The project follows CI/CD best practices:

* Version-controlled on GitHub
* Container-based deployment
* Secrets stored securely (no secrets in code)
* Ready for GitHub Actions integration


---

## 📂 Project Structure

```
EasyVault.KRD/
├── app/
│   ├── config/
│   ├── lib/
│   ├── public/
│   ├── security/
├── docker/
│   ├── php/
│   └── mysql/
├── docker-compose.yml
├── composer.json
├── .env (not committed)
└── README.md
```

---

## 🚀 Setup Instructions

### Prerequisites

* Docker & Docker Compose
* Git

### Local Setup

```bash
git clone https://github.com/your-repo/EasyVault.KRD.git
cd EasyVault.KRD
docker compose up -d --build
```

Access the application at:

```
http://localhost:8080
```

---





---

## 📜 License

This project is for academic and educational purposes.
