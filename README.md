# Student Management application 
A complete Student Management System built using PHP, Python Flask, MySQL, Docker, and AWS EC2.

This project provides separate login systems for Admin and Students.

## Project architecture
```shell
student_management_system/
│
├── admin/
│   ├── admin_login.php
│   ├── admin_dashboard.php
│   ├── add_student.php
│   ├── manage_marks.php
│   ├── manage_attendance.php
│   └── logout.php
│
├── student/
│   ├── register.php
│   ├── student_login.php
│   ├── student_dashboard.php
│   ├── view_marks.php
│   ├── view_attendance.php
│   └── logout.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│
├── config/
│   └── db.php
│
├── python/
│   ├── app.py
│   ├── attendance_report.py
│   ├── requirements.txt
│   └── Dockerfile
│
├── sql/
│   └── database.sql
│
├── Dockerfile
│
├── docker-compose.yml
│
├── .gitignore
│
├── README.md
│
└── index.php
````

## Features
### Admin Features
````shell
Admin Login
Add Students
Manage Student Marks
Manage Attendance
View All Students
Manage Student Records
Dashboard Access
Session Authentication
````
### Student Features
````shell
Student Registration
Student Login
View Marks
View Attendance
Dashboard Access
````
### Python Flask API
````shell
Total Student Count API
MySQL Database Integration
Flask Backend Support
````
# Create a Ec2 instance
ubunut ,t3.small , student-key.pem ,student-sg ,20gb

**student-sg**  inbound rules

| Type       | Port |
| ---------- | ---- |
| SSH        | 22   |
| HTTP       | 80   |
| Custom TCP | 8080 |
| Custom TCP | 5000 |


## Connect to EC2
```shell
ssh -i Donwloads/student-key.pem ubuntu@EC2-PUBLIC-IP
```
## 2️⃣ Update Ubuntu
```shell
sudo apt update
sudo apt upgrade -y
```
## 3️⃣ Install Git
```shell
sudo apt install git -y
```
##  4️⃣ Install Docker Engine
```shell
sudo apt install docker.io -y

Verify:

docker --version
```

## 5️⃣ Enable Docker
```shell
sudo systemctl enable docker
sudo systemctl start docker

Check status:

sudo systemctl status docker

Press:

q
```

## Correct way to install Docker Compose V2 on Ubuntu


### Step 1: Set up Docker’s official repository
```bash
sudo apt update
sudo apt install ca-certificates curl gnupg lsb-release -y

sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

```
### Step 2: Update package index
```bash
  sudo apt update
 ``` 
### Step 3: Install Docker Compose plugin
```bash
  sudo apt install docker-compose-plugin -y

  docker compose version

```
  **Add your user to the Docker group**

This allows you to run Docker commands without sudo:

# Create the docker group if it doesn't exist
```bash
sudo groupadd docker
```
# Add your user to the docker group
```bash
sudo usermod -aG docker $USER
```
# Apply the group change (you need to log out and back in, or run:)
```bash
newgrp docker
```
# Test
```shell
docker ps
git clone 

docker compose up --build -d
````
## Access Application
### PHP Application
http://YOUR-EC2-IP:8080
### Flask API
http://YOUR-EC2-IP:5000
### Default Admin Login

| Username       | Password |
| ---------- | ---- |
| admin        | admin123   |



docker compose down -v



  
