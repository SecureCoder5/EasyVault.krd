# EasyVault.krd
# 🔐 EasyVault.krd – Secure Password Manager (DevSecOps Project)

EasyVault.krd is a secure, PHP-based password manager web application designed to demonstrate core **DevSecOps principles**, including secure application development, Docker containerization, and automated CI/CD with integrated security scanning.

The project goes beyond a basic CRUD application by implementing real-world security controls such as encryption, role-based access control, audit logging, and automated security analysis.

---

## 📌 Project Objectives

- Build a secure web application using PHP and MySQL
- Apply secure coding practices
- Containerize the application using Docker
- Implement a CI/CD pipeline using GitHub Actions
- Integrate security scanning into the pipeline
- Document security decisions and architecture

---

## 🛠️ Technology Stack

| Component | Technology |
|--------|-----------|
| Backend | PHP 8.2 |
| Web Server | Apache |
| Database | MySQL |
| Containerization | Docker & Docker Compose |
| CI/CD | GitHub Actions |
| Dependency Security | Composer Audit |
| Container Security | Trivy |
| Version Control | Git & GitHub |

---

## 📂 Project Structure

EasyVault.krd/
├── app/
│ ├── config/
│ ├── lib/
│ ├── public/
│ └── security/
├── docker/
│ ├── php/
│ └── mysql/
├── .github/workflows/
│ └── cicd.yml
├── docker-compose.yml
├── .gitignore
└── README.md



---

## 🔑 Core Features

### Authentication & Accounts
- Secure user registration with password hashing (bcrypt)
- Email verification using one-time passwords (OTP)
- Login restricted until account verification
- Password reset with time-limited tokens
- Secure session handling

### Password Vault
- Add, edit, and delete credentials
- AES-GCM encryption for stored passwords
- Decryption only occurs at runtime
- Ownership enforced at database level

### Admin Panel
- Role-based access control (admin / user)
- Enable or disable user accounts
- Promote or demote users
- Admin actions are fully logged
- Admins cannot access user vault data

### Audit Logging
- Records administrative actions
- Logs include:
  - Action type
  - Actor
  - Target user
  - IP address
  - Timestamp

---

## 🔒 Security Practices Implemented

- Prepared SQL statements (SQL injection prevention)
- Password hashing using bcrypt
- AES-GCM encryption for sensitive data
- Secure token generation and hashing
- OTP expiration handling
- Session regeneration on login
- Access control guards for all protected routes
- No plaintext secrets stored in the repository

---

## 🐳 Docker Setup

The application runs using Docker Compose with separate containers for:
- PHP + Apache
- MySQL database

### Run Locally with Docker

```bash
docker compose up --build

accessible at http://localhost:8080
 since hosted locally
