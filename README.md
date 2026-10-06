# Student Management CI/CD, Kubernetes, Monitoring & Logging Notes

### for reference check 54 student app cicd.docx file in the repository

I want one EC2 instance and a simple beginner-friendly implementation, I would simplify the architecture.
Use the EC2 instance as your CI tools/server machine and use GitHub Actions as CI. We will still deploy the application to EKS using Argo CD. Don't install unnecessary tools all at once.
```text
What we are going to build
YOUR LAPTOP
    │
    │ git push
    ▼
GitHub Repository
    │
    ▼
GitHub Actions
    │
    ├── PHP validation
    ├── Python validation
    ├── Gitleaks
    ├── SonarQube scan
    ├── Docker build
    ├── Docker push
    └── Update Kubernetes YAML
                │
                ▼
             GitHub
                │
                ▼
             Argo CD
                │
          Automatic Sync
                │
                ▼
              EKS
        ┌───────┼────────┐
        ▼       ▼        ▼
       PHP    Python    MySQL
Your EC2 will initially be our administration/tool server:
EC2
├── Git
├── Docker
├── AWS CLI
├── kubectl
├── eksctl
└── SonarQube
```
I recommend Ubuntu 24.04, t3.large, about 30 GB disk while SonarQube is running on it. t3.medium or c7.flexlarge can become uncomfortable once you combine SonarQube and your tooling.

---


## PART 1 — Create EC2

In AWS Console:
EC2 → Instances → Launch instance
Use approximately:
Name: **student-cicd-server**

AMI: **Ubuntu Server 24.04 LTS**

Instance type: **c7i-flex.large**

Storage: **40 GB**

Key pair: 
Create/select your .pem key

**For the Security Group initially allow:**
```bash
Port	Purpose	Source
22	SSH	My IP
9000	SonarQube	anywhereip
```
 Don't expose port 9000 to 0.0.0.0/0 unless you have a specific reason.
Launch it.

---


## PART 2 — Connect to EC2

From Git Bash on Windows:
```bash
chmod 400 your-key.pem
```
Then:
```bash
ssh -i your-key.pem ubuntu@YOUR_EC2_PUBLIC_IP
```
You should now see something similar to:
```bash
ubuntu@ip-172-31-x-x:~$
```
From this point, commands marked EC2 are executed here.

---


## PART 3 — Update EC2

EC2
```bash
sudo hostnamectl set-hostname studentserver
```
```bash
/bin/bash
```
```bash
sudo apt update
```
```bash
sudo apt upgrade -y
```
Install basic tools:
```bash
sudo apt install -y \
  git \
  curl \
  wget \
  unzip \
  jq
```
Check:
```bash
git --version
```

---


## PART 4 — Clone your repository

EC2
```bash
cd ~
```
Then:
```bash
git clone https://github.com/harathi-mutyam/student-app-cicd.git
```
Enter it:
```bash
cd student-app-cicd
```
Check:
```bash
ls
```
You should see your project files such as:
```bash
admin/   , student/ , assets/ , config/ , python/ , sql/ , Dockerfile ,
docker-compose.yml , index.php , README.md
```
This is your project directory.

---


## PART 5 — Install Docker

open EC2 gitbash terminal 


Use Docker's Ubuntu repository rather than the older Ubuntu docker.io package. Docker's current Ubuntu installation instructions use its apt repository and packages including docker-ce, docker-ce-cli, containerd.io, Buildx, and the Compose plugin. 


Run:
```bash
sudo apt update
```
```bash
sudo apt install -y ca-certificates curl
```
Create the key directory:
```bash
sudo install -m 0755 -d /etc/apt/keyrings
```
Add Docker's signing key:
```bash
sudo curl -fsSL \
https://download.docker.com/linux/ubuntu/gpg \
-o /etc/apt/keyrings/docker.asc
```
```bash
sudo chmod a+r /etc/apt/keyrings/docker.asc
```
Add Docker repository:
```bash
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}") stable" \
  | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
```
Install:
```bash
sudo apt update
```
```bash
sudo apt install -y \
  docker-ce \
  docker-ce-cli \
  containerd.io \
  docker-buildx-plugin \
  docker-compose-plugin
```
Check:
```bash
sudo docker --version
```
and:
```bash
sudo docker compose version
```

---


## PART 6 — Allow Ubuntu user to run Docker

EC2
```bash
sudo usermod -aG docker ubuntu
```
Then:
```bash
exit
```
```bash
exit
```
SSH into EC2 again:
```bash
ssh -i your-key.pem ubuntu@YOUR_EC2_PUBLIC_IP
```
Test:
```bash
docker ps
```
If that works without sudo, Docker is ready.

---


## PART 7 — First local application test

Before CI/CD, make sure the application actually works.
EC2
```bash
cd ~/student-app-cicd
```
Run:
```bash
docker compose build
```
Then:
```bash
docker compose up -d
```
Check:
```bash
docker compose ps
```
Ideally you'll see your PHP, Python and MySQL services.
Check logs:
```bash
docker compose logs
```
```text
If everything is running, we've proved:
Source Code
     ↓
Docker Build
     ↓
Containers
     ↓
Application
```
This is an important milestone.

---


## PART 8 — Test PHP

If your docker-compose.yml maps:  8080:80

temporarily add **Security Group**:

```bash
TCP 8080
Source: My IP
```
Then open:  http://EC2-PUBLIC-IP:8080


You should see your Student Management System.

Open as admin--> admin details--> username: admin password: admin123
If it doesn't work, fix this before proceeding to Kubernetes.

```bash
docker compose down
```

---


## PART 9 — Install AWS CLI

EC2
Run:
```bash
cd ~
```
```bash
curl \
"https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" \
-o awscliv2.zip
```
```bash
unzip awscliv2.zip
```
```bash
sudo ./aws/install
```
Check:
```bash
aws --version
```

---


## PART 10 — AWS authentication

For the simplest learning setup:
Pen browser create access keys and secret keys for aiam user .use those keys.

```bash
aws configure
```
Enter:
AWS Access Key ID:
<your key>

AWS Secret Access Key:
<your secret>

Default region:
eu-central-1

Default output:
json
Then test:
```bash
aws sts get-caller-identity
```
You should receive your AWS account/user information.

---


## PART 11 — Install kubectl

EC2
Because Kubernetes versions change, follow the current official installation rather than copying an old binary URL. The Kubernetes documentation provides the Linux kubectl installation procedure. 
For x86-64 Ubuntu:
```bash
curl -LO \
"https://dl.k8s.io/release/$(curl -L -s \
https://dl.k8s.io/release/stable.txt)/bin/linux/amd64/kubectl"
```
Install:
```bash
sudo install -o root -g root \
-m 0755 kubectl \
/usr/local/bin/kubectl
```
Check:
```bash
kubectl version --client
```

---


## PART 12 — Install eksctl

EC2
Run:
```bash
ARCH=amd64
```
```bash
PLATFORM=$(uname -s)_$ARCH
```
```bash
curl -sLO \
"https://github.com/eksctl-io/eksctl/releases/latest/download/eksctl_$PLATFORM.tar.gz"
```
Extract:
```bash
tar -xzf eksctl_$PLATFORM.tar.gz
```
Install:
```bash
sudo mv eksctl /usr/local/bin/
```
Check:
eksctl version
```text
Now your EC2 has:
Git       ✓
Docker    ✓
AWS CLI   ✓
kubectl   ✓
eksctl    ✓
```

---

For a real production setup, I'd use an EC2 IAM role instead of long-lived access keys. We can upgrade to that after your first working pipeline.

```bash
cd ~/student-app-cicd
```
```bash
sudo apt update
```
```bash
sudo apt install -y gnupg software-properties-common
```

Add HashiCorp key:
```bash
wget -O- https://apt.releases.hashicorp.com/gpg \
| gpg --dearmor \
| sudo tee /usr/share/keyrings/hashicorp-archive-keyring.gpg > /dev/null
```

Add repository:
```bash
echo "deb [signed-by=/usr/share/keyrings/hashicorp-archive-keyring.gpg] \
https://apt.releases.hashicorp.com \
$(lsb_release -cs) main" \
| sudo tee /etc/apt/sources.list.d/hashicorp.list
```

Then:
```bash
sudo apt update
```
```bash
sudo apt install -y terraform
```

```bash
cd terraform
```
```bash
terraform --version
```
```bash
terraform init
```
```bash
terraform apply -var-file="dev.tfvars"
```
press yes then press enter key	

```bash
cd ..
```

```bash
aws eks update-kubeconfig \
  --region eu-central-1 \
  --name student-cluster
```


When finished:
```bash
kubectl get nodes
```
You should see:
NAME                       STATUS
ip-xxx                     Ready
ip-xxx                     Ready
```text
At this point:
EC2
 │
 │ kubectl
 ▼
EKS
├── Node 1
└── Node 2
```

---


## PART 14 — Create Kubernetes directories

EC2
```bash
cd ~/student-app-cicd
```
```bash
mkdir -p k8s
```
```bash
mkdir -p argocd
```
```bash
mkdir -p .github/workflows
```
Now:
```bash
ls
```
should include:
.github  , argocd , k8s

---


## PART 15 — Create namespace

EC2
Create:
```bash
vim k8s/namespace.yaml
```
Paste:
```yaml
apiVersion: v1
kind: Namespace

metadata:
  name: student-management
```
Save:
Esc key --> :wq!→ Enter
Apply:
```bash
kubectl apply -f k8s/namespace.yaml
```
Verify:
```bash
kubectl get namespaces
```

Now I want to push these changes to github repository from ec2 instance

```bash
git status
```
```bash
git diff
```
```bash
git add k8s/   # here give changed file names and folders only
```
```bash
git status
```
```bash
git commit -m "Update database configuration and add Kubernetes manifests"
```


GitHub Authentication from EC2

### Step 1 — Configure Git username and email

```bash
git config --global user.name "harathi-mutyam"
```
```bash
git config --global user.email "ehmutyam@gmail.com"
```
Check:
```bash
git config --global user.name
```
```bash
git config --global user.email
```

### Step 2 — Create GitHub Personal Access Token

In GitHub: Profile → Settings  → Developer settings → Personal access tokens → Fine-grained tokens → Generate new token  
Select:  Repository: student-app-cicd
Permission:
Contents → Read and write  , Actions --> Read and write
Generate and copy the token.
Do not share or save the token inside your project.

### Step 3 — Check changes

```bash
cd ~/student-app-cicd
```
```bash
git status
```

### Step 4 — Add files

```bash
git add .  or git add k8s/
```

### Step 5 — Commit

```bash
git commit -m "Update database configuration and add Kubernetes manifests"
```

### Step 6 — Push to GitHub

```bash
git push origin main
```
When asked:
Username: Enter  harathi-mutyam
When asked:
Password: Paste the GitHub Personal Access Token, not your normal GitHub password.

### Step 7 — Verify

```bash
git status
```
Expected: On branch main
Your branch is up to date with 'origin/main'.
nothing to commit, working tree clean

Normal workflow later
Whenever you change your project:
```bash
git add .  or git add <changedfiles names>
```
```bash
git commit -m "your message"
```
```bash
git push origin main
```
Username for 'https://github.com': harathi-mutyam
Password for 'https://harathi-mutyam@github.com':
Paste your guthub authentication token here  -->press enter

---


## PART 16 — Create database secret

Don't save your real password in Git.
Run:
```bash
vim k8s/mysql-secret.yaml
```
```yaml
apiVersion: v1
kind: Secret

metadata:
  name: mysql-secret
  namespace: student-management

type: Opaque

stringData:
  mysql-root-password: root
  mysql-user: studentuser
  mysql-password: student123
```

```bash
kubectl apply -f k8s/mysql-secret.yaml
```
```bash
kubectl get secret mysql-secret -n student-management
```
you should see : mysql-secret

---


## PART 17 — Create MySQL PVC

EC2
```bash
vim k8s/mysql-pvc.yaml
```
Paste:
```yaml
apiVersion: v1
kind: PersistentVolumeClaim

metadata:
  name: mysql-pvc
  namespace: student-management

spec:
  accessModes:
    - ReadWriteOnce

 storageClassName: gp3
  resources:
    requests:
      storage: 5Gi
```
Save it.

---


## PART 18 — MySQL Deployment

Create:
```bash
vim k8s/mysql-deployment.yaml
```
Paste:
```yaml
apiVersion: apps/v1
kind: Deployment

metadata:
  name: mysql
  namespace: student-management

spec:
  replicas: 1

  selector:
    matchLabels:
      app: mysql

  template:
    metadata:
      labels:
        app: mysql

    spec:
      containers:
        - name: mysql

          image: mysql:8.4

          ports:
            - containerPort: 3306

          env:
            - name: MYSQL_DATABASE
              value: student_management

            - name: MYSQL_USER
              valueFrom:
                secretKeyRef:
                  name: mysql-secret
                  key: mysql-user

            - name: MYSQL_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mysql-secret
                  key: mysql-password

            - name: MYSQL_ROOT_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mysql-secret
                  key: mysql-root-password

          volumeMounts:
            - name: mysql-storage
              mountPath: /var/lib/mysql

      volumes:
        - name: mysql-storage
          persistentVolumeClaim:
            claimName: mysql-pvc
```

---


## PART 19 — MySQL Service

Create:
```bash
vim k8s/mysql-service.yaml
```
Paste:
```yaml
apiVersion: v1
kind: Service

metadata:
  name: mysql
  namespace: student-management

spec:
  selector:
    app: mysql

  ports:
    - port: 3306
      targetPort: 3306

  type: ClusterIP
```
```text
Now inside Kubernetes:
PHP
 │
 │ DB_HOST=mysql
 ▼
mysql Service
 │
 ▼
MySQL Pod
```

---


## PART 20 — PHP Kubernetes deployment

Create:
```bash
vim k8s/php-deployment.yaml
```
Paste:
```yaml
apiVersion: apps/v1
kind: Deployment

metadata:
  name: student-management-php
  namespace: student-management

spec:
  replicas: 2

  selector:
    matchLabels:
      app: student-management-php

  template:
    metadata:
      labels:
        app: student-management-php

    spec:
      containers:
        - name: php

          image: YOUR_DOCKER_USERNAME/student-management-php:initial

          ports:
            - containerPort: 80

          env:
            - name: DB_HOST
              value: mysql

            - name: DB_NAME
              value: student_management

            - name: DB_USER
              valueFrom:
                secretKeyRef:
                  name: mysql-secret
                  key: mysql-user

            - name: DB_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mysql-secret
                  key: mysql-password
```
Replace:
YOUR_DOCKER_USERNAME with your actual Docker Hub username.
My Docker username is harathi2026

---


## PART 21 — PHP Service

Create:
```bash
vim k8s/php-service.yaml
```
Paste:
```yaml
apiVersion: v1
kind: Service

metadata:
  name: student-management-php
  namespace: student-management

spec:
  selector:
    app: student-management-php

  ports:
    - port: 80
      targetPort: 80

  type: LoadBalancer
```

---


## PART 22 — Python deployment

Create:
```bash
vim k8s/python-deployment.yaml
```
Paste:
```yaml
apiVersion: apps/v1
kind: Deployment

metadata:
  name: student-management-python
  namespace: student-management

spec:
  replicas: 2

  selector:
    matchLabels:
      app: student-management-python

  template:
    metadata:
      labels:
        app: student-management-python

    spec:
      containers:
        - name: python

          image: harathi2026/student-management-python:initial

          ports:
            - containerPort: 5000

          env:
            - name: DB_HOST
              value: mysql

            - name: DB_NAME
              value: student_management

            - name: DB_USER
              valueFrom:
                secretKeyRef:
                  name: mysql-secret
                  key: mysql-user

            - name: DB_PASSWORD
              valueFrom:
                secretKeyRef:
                  name: mysql-secret
                  key: mysql-password
```
Again replace:
YOUR_DOCKER_USERNAME

---


## PART 23 — Python Service

Create:
```bash
vim k8s/python-service.yaml
```
Paste:
```yaml
apiVersion: v1
kind: Service

metadata:
  name: student-management-python
  namespace: student-management

spec:
  selector:
    app: student-management-python

  ports:
    - port: 5000
      targetPort: 5000

  type: ClusterIP
```

---


## PART 24 — Build initial images

Before Argo CD can deploy, Docker Hub needs the initial images.
EC2
Login:
```bash
docker login -u <username:harathi2026>
```
password:enter your docker hub password
Build PHP:
```bash
docker build \
  -t harathi2026/student-management-php:initial .
```
Push:
```bash
docker push \
  harathi2026/student-management-php:initial
```
Build Python:
```bash
docker build \
  -t harathi2026/student-management-python:initial \
  ./python
```
Push:
```bash
docker push \
  harathi2026/student-management-python:initial
```

---


## PART 25 — First Kubernetes deployment

Now:
```bash
kubectl apply -f k8s/namespace.yaml
```
```bash
kubectl apply -f k8s/mysql-secret.yaml
```

```bash
kubectl apply -f k8s/
```
Check:
```bash
kubectl get pods -n student-management
```
```text
You want:
mysql-xxxxx                       Running

student-management-php-xxxxx     Running
student-management-php-xxxxx     Running
student-management-python-xxxxx  Running
student-management-python-xxxxx  Running
```

here you can not get running for sql pod so run these command because you are not using root user in database so run these commands in root folder
Import the database tables
```bash
cd ~/student-app-cicd
```

```bash
kubectl exec -i deployment/mysql -n student-management -- \
mysql -u root -proot student_management < sql/database.sql
```

Verify database tables
```bash
kubectl exec -it deployment/mysql -n student-management -- mysql -u root -p
```

Enter the root password: root
Then:
```sql
USE student_management;
SHOW TABLES;
SELECT * FROM admins;
```
Your current SQL initializes:
username = admin
password = admin123

exit;
Then:
```bash
kubectl get all -n student-management
```
```bash
kubectl get svc -n student-management
```
Find:
student-management-php
and its:
EXTERNAL-IP
In AWS it will usually be a load-balancer DNS hostname.
Open it in your browser.
Select --> admin--> admin admin123
```text
At this stage:
Internet --> AWS Load Balancer  --> PHP Service  --> PHP Pods  -->  MySQL Service  --> MySQL
```

If loadbalacer not working steps for trouble shooting
Useful Commands – EKS LoadBalancer Troubleshooting
1. Check all resources
```bash
kubectl get all -n student-management
```
2. Check pods
```bash
kubectl get pods -n student-management
```
3. Check services and LoadBalancer URL
```bash
kubectl get svc -n student-management
```
4. Check PHP service
```bash
kubectl get svc student-management-php -n student-management -o wide
```
5. Check PHP service details
```bash
kubectl describe svc student-management-php -n student-management
```
6. Check service endpoints
```bash
kubectl get endpoints student-management-php -n student-management
```
7. Test application from EC2
```bash
curl -v --max-time 10 http://<ELB-URL>/
```
Expected:
HTTP/1.1 200 OK
8. Check ELB instance health
```bash
aws elb describe-instance-health \
  --region eu-north-1 \
  --load-balancer-name <ELB-NAME>
```
Expected:
State: InService
9. Test from Windows
curl.exe -v --max-time 10 http://<ELB-URL>/
Expected:
HTTP/1.1 200 OK
10. If curl works but browser does not
Go to:
Windows Settings
→ Network & Internet
→ Proxy
→ Automatically detect settings → OFF

 

Then reopen the browser and use:
http://<ELB-URL>/

Check AWS LoadBalancer Health
```bash
aws elb describe-instance-health \
  --region eu-north-1 \
  --load-balancer-name <LOAD-BALANCER-NAME>
```




## PART 26 — Install Argo CD

EC2
```bash
kubectl create namespace argocd
```
Then:
```bash
kubectl apply \
  -n argocd \
  --server-side \
  --force-conflicts \
  -f https://raw.githubusercontent.com/argoproj/argo-cd/stable/manifests/install.yaml
```
Check:
```bash
kubectl get pods \
  -n argocd
```
Wait until they are running.
The official Argo CD getting-started guide uses this installation manifest and argocd namespace. 

---


## PART 27 — Argo CD Application creation first time on second skip this step if you pause the ec2 instance

Create:
```bash
vim argocd/application.yaml
```
Paste:
```yaml
apiVersion: argoproj.io/v1alpha1
kind: Application

metadata:
  name: student-management
  namespace: argocd

spec:
  project: default

  source:
    repoURL: https://github.com/harathi-mutyam/student-app-cicd.git
    targetRevision: main
    path: k8s

  destination:
    server: https://kubernetes.default.svc
    namespace: student-management

  syncPolicy:
    automated:
      prune: true
      selfHeal: true

    syncOptions:
      - CreateNamespace=true
```
Apply:
```bash
kubectl apply \
  -f argocd/application.yaml
```
Check:
```bash
kubectl get applications \
  -n argocd
```
This is your CD engine.

```bash
kubectl get pods -n argocd
```

```bash
kubectl get svc -n argocd
```

Start port-forwarding on EC2
```bash
kubectl port-forward svc/argocd-server -n argocd 8080:443 --address=0.0.0.0
```

Allow port 8080 in the EC2 Security Group
Go to:
AWS Console → EC2 → Instances → your student server → Security → Security Groups → Inbound rules → Edit inbound rules
Type:        Custom TCP
Port:        8080
Source:      My IP
Description: Temporary Argo CD access

Copy public ip of student server
Open browser-->paste the publicip:8080
Username: admin
Password:

Open another gitbash terminal
```bash
ssh -i Downloads/student-cicd-key.pem ubuntu@13.49.138.168
```
```bash
cd student-app-cicd
```
```bash
# 1. List all secrets in the Argo CD namespace
kubectl get secrets -n argocd

# 2. Describe the Argo CD initial admin secret
kubectl describe secret argocd-initial-admin-secret -n argocd

# 3. Decode and display the Argo CD admin password
kubectl -n argocd get secret argocd-initial-admin-secret \
  -o jsonpath="{.data.password}" | base64 -d && echo

```
Copy password --> paste it in browser  --> login into argo cd and observe it
Username: admin
Password: <password from command>


 
Press ctrl + c in the first git bash terminal

---


## PART 28 — Install SonarQube on EC2 first time

We'll use Docker because it's easier for a beginner.
EC2
Run:
```bash
docker volume create sonarqube_data
```
Then:
```bash
docker run -d \
  --name sonarqube \
  --restart unless-stopped \
  -p 9000:9000 \
  -v sonarqube_data:/opt/sonarqube/data \
  sonarqube:lts-community
```

We want to eventually see: SonarQube is operational

Check:
```bash
docker ps
```
Open:
http://YOUR_EC2_PUBLIC_IP:9000


Initial SonarQube credentials are normally:
```text
admin
```
```text
admin
```
Change the password when prompted.

**if you are doing second time after pause and start the ec2 instance
try below steps with yellow colour**

```bash
docker ps -a --filter name=sonarqube
```
```bash
docker start sonarqube
```
```bash
docker ps --filter name=sonarqube
```
```bash
docker logs --tail 30 sonarqube
```
http://YOUR_EC2_PUBLIC_IP:9000
```bash
username: admin

password: admin123
```

if you are using the ec2 instance server after pause and start skip step 29


---


## PART 29 — Create SonarQube project

Inside SonarQube:
Create Project → manually--> Local project
Use:
Project display name:
Student Management System

Project key:
student-management-system
Then generate a token.
Copy it somewhere temporarily.
We'll put it into GitHub Secrets.
Change the SONAR_HOST_URL in github actions variables

---


## PART 30 — Docker Hub token

Go to Docker Hub account settings/security and create an access token.
You need:
Docker Hub username
Docker Hub token
Do not put your Docker Hub password directly in ci.yml.

---


## PART 31 — GitHub Secrets

Open your GitHub repository:
Settings → Secrets and variables → Actions
Create:
DOCKERHUB_USERNAME
value:
your Docker Hub username
Create:
DOCKERHUB_TOKEN
value:
your Docker Hub token
Create:
SONAR_TOKEN
value:
SonarQube token
Create:
SONAR_HOST_URL
value:
http://EC2_PUBLIC_IP:9000

---


### IMPORTANT — SonarQube + GitHub-hosted runner problem

There's one architectural issue beginners often miss.
GitHub-hosted runners must be able to reach:
EC2:9000
If your EC2 security group allows port 9000 only from your laptop IP, GitHub Actions cannot reach SonarQube.
```text
Don't solve that by permanently exposing SonarQube to the whole internet.
For your project, a better solution is:
GitHub
   ↓
Self-hosted GitHub Actions Runner
   │
   │ running on EC2
   ├── SonarQube localhost:9000
   ├── Docker
   └── Git
This also makes your project more realistic.
```
So let's use your EC2 as the self-hosted CI runner.

---


## PART 32 — Add EC2 as GitHub self-hosted runner

On GitHub:
Repository → Settings → Actions → Runners
Click:
New self-hosted runner
Select:
Linux
x64
GitHub will give you commands similar to:
```bash
mkdir actions-runner
```
```bash
cd actions-runner
```
and a current download command.
Use the exact commands GitHub shows you.
Then GitHub gives you something similar to:
```bash
./config.sh \
  --url https://github.com/harathi-mutyam/student-app-cicd \
  --token XXXXX
```

Enter the name of the runner group to add this runner to: [press Enter for Default] press enter key here

Enter the name of runner: [press Enter for studentserver] studentserver-runner

This runner will have the following labels: 'self-hosted', 'Linux', 'X64'
Enter any additional labels (ex. label-1,label-2): [press Enter to skip] self-hosted

√ Runner successfully added

# Runner settings

Enter name of work folder: [press Enter for _work] press enter here


Again, use the exact temporary token GitHub provides.
Then:
```bash
./run.sh
```
ubuntu@studentserver:~/actions-runner$ ./run.sh

√ Connected to GitHub

Current runner version: '2.337.0'
2026-10-05 06:51:46Z: Listening for Jobs

On GitHub you should see:
Runner status:
```text
Idle
```

---


## PART 33 — Run the runner as a service

After configuring it, stop ./run.sh with:
Ctrl+C
Then from inside actions-runner:
```bash
sudo ./svc.sh install
```
Start:
```bash
sudo ./svc.sh start
```
Check:
```bash
sudo ./svc.sh status
```
Now the runner survives SSH logout/restart.

---


## PART 34 — CI/CD workflow

Now create:
```bash
cd ~/student-app-cicd
```
```bash
vim .github/workflows/ci.yml
```
For your beginner implementation, use the EC2 runner for all jobs:
```yaml
name: Student Management CI/CD
on:
  push:
    branches:
      - main

  workflow_dispatch:

permissions:
  contents: write

env:
  PHP_IMAGE: ${{ vars.DOCKERHUB_USERNAME }}/student-management-php
  PYTHON_IMAGE: ${{ vars.DOCKERHUB_USERNAME }}/student-management-python

jobs:

  ci:
    name: Test and Security
    runs-on: self-hosted

    steps:

      - name: Checkout repository
        uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - name: PHP Syntax Check
        run: |
          docker run --rm \
            -v "$PWD:/app" \
            -w /app \
            php:8.2-cli \
            sh -c 'find . -name "*.php" -exec php -l {} \;'

      - name: Python Syntax Check
        run: |
          python3 -m compileall python/

      - name: Gitleaks Secret Scan
        uses: gitleaks/gitleaks-action@v2
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}

      - name: SonarQube Scan
        uses: SonarSource/sonarqube-scan-action@v6
        env:
          SONAR_TOKEN: ${{ secrets.SONAR_TOKEN }}
          SONAR_HOST_URL: ${{ vars.SONAR_HOST_URL }}


  docker:
    name: Build and Push Images

    needs:
      - ci

    runs-on: self-hosted

    steps:

      - name: Checkout repository
        uses: actions/checkout@v4

      - name: Create Image Tag
        run: |
          echo "IMAGE_TAG=${GITHUB_SHA::7}" >> $GITHUB_ENV

      - name: Docker Login
        uses: docker/login-action@v3
        with:
          username: ${{ vars.DOCKERHUB_USERNAME }}
          password: ${{ secrets.DOCKERHUB_TOKEN }}

      - name: Build PHP Image
        run: |
          docker build \
            -t $PHP_IMAGE:$IMAGE_TAG \
            .

      - name: Push PHP Image
        run: |
          docker push \
            $PHP_IMAGE:$IMAGE_TAG

      - name: Build Python Image
        run: |
          docker build \
            -t $PYTHON_IMAGE:$IMAGE_TAG \
            ./python

      - name: Push Python Image
        run: |
          docker push \
            $PYTHON_IMAGE:$IMAGE_TAG


      - name: Update Kubernetes Images
        run: |

          sed -i \
          "s|image: .*student-management-php:.*|image: $PHP_IMAGE:$IMAGE_TAG|" \
          k8s/php-deployment.yaml

          sed -i \
          "s|image: .*student-management-python:.*|image: $PYTHON_IMAGE:$IMAGE_TAG|" \
          k8s/python-deployment.yaml


      - name: Commit Kubernetes Changes
        run: |

          git config user.name "github-actions[bot]"

          git config user.email \
          "41898282+github-actions[bot]@users.noreply.github.com"

          git add \
            k8s/php-deployment.yaml \
            k8s/python-deployment.yaml

          if git diff --cached --quiet
          then
            echo "No changes"
            exit 0
          fi

          git commit \
            -m "ci: deploy $IMAGE_TAG"

          git push
```
I've deliberately kept this workflow simpler than the previous version.
First get this working.
Later we can separate it into:
php-ci
python-ci
gitleaks
sonarqube
docker
gitops-update
as independent jobs.

---


## PART 35 — Add sonar-project.properties

At project root:
```bash
vim sonar-project.properties
```
Use:
sonar.projectKey=Student-Management-System
sonar.projectName=Student Management System

sonar.sources=admin,student,config,python,index.php

sonar.sourceEncoding=UTF-8

sonar.python.version=3

sonar.exclusions=**/vendor/**,**/__pycache__/**,k8s/**,argocd/**

Add for your existing token need to add the missing workflow permission.
Go to GitHub → Profile picture → Settings → Developer settings → Personal access tokens → Tokens—> select your Token --> edit -->select 
☑ workflow
   Update GitHub Action workflows



---


## PART 36 — Commit your DevOps files

EC2
```bash
git status
```
Then:
```bash
git add  .github/   argocd/ sonar-project.properties 
```
```bash
git commit -m "Add complete CI CD pipeline"
```
```bash
git push origin main
```

---


## PART 37 — Watch GitHub Actions

Go to:
GitHub repository → Actions
```text
You should see:
Student Management CI/CD

Test and Security
       ↓
       ✓
       ↓
Build and Push Images
       ↓
       ✓
During CI:
PHP syntax
     ↓
Python syntax
     ↓
Gitleaks
     ↓
SonarQube
     ↓
Docker PHP
     ↓
Docker Python
     ↓
Docker Hub
```

---


## PART 38 — Automatic GitOps deployment

Suppose the commit SHA is:
93f21a7
```text
CI creates:
Docker Hub

student-management-php:93f21a7

student-management-python:93f21a7
Then CI changes:
image: username/student-management-php:initial
to:
image: username/student-management-php:93f21a7
 
and commits that back to Git.
Then:
GitHub
   │
   │ changed K8s YAML
   ▼
Argo CD
   │
   │ detects change
   ▼
Automatic Sync
   │
   ▼
EKS
   │
   ├── New PHP pods
   └── New Python pods
```
You don't run:
```bash
kubectl apply
```
after each application change.
That's the whole point of Argo CD GitOps.

---


## PART 39 — Your normal developer workflow

Once everything works, your daily procedure becomes extremely simple.
Change: index.php page content
```bash
cd ~/student-app-cicd
```
```bash
vim index.php
```
```bash
git status
```
Then:
```bash
git add index.php
```
```bash
git commit -m "Update student home page"
```
```bash
git push origin main
```
Enter:
Username: harathi-mutyam
Password: <GitHub Personal Access Token>
If Push Is Rejected Because GitHub Has New Changes
rejected
fetch first
remote contains work that you do not have locally

Reason: GitHub contains a newer commit, usually because GitHub Actions updated the Kubernetes YAML.

Update EC2 Repository After CI/CD .

```bash
git pull --rebase origin main
```
```bash
git push origin main
```
it ask username and token enter it will success now

```text
Everything else happens automatically:
YOU

git push
   │
   ▼
GitHub
   │
   ▼
Self-hosted GitHub Runner
   │
   ├── Test
   ├── Gitleaks
   ├── SonarQube
   ├── Docker Build
   ├── Docker Push
   └── Update YAML
             │
             ▼
           GitHub
             │
             ▼
           Argo CD
             │
             ▼
            EKS
             │
             ▼
      New Application
```

---


## PART 40 — Verify deployment

From EC2:
```bash
kubectl get pods \
  -n student-management
```
Then:
```bash
kubectl get deployments \
  -n student-management
```
Check the deployed image:
```bash
kubectl get deployment \
  student-management-php \
  -n student-management \
  -o=jsonpath='{.spec.template.spec.containers[0].image}'
```
You should see something like:
yourusername/student-management-php:93f21a7
Check services:
```bash
kubectl get svc \
  -n student-management
```

---


## PART 41 — Demonstrate self-healing

Your Git says:
replicas: 2
Run manually:
```bash
kubectl scale deployment  student-management-php   --replicas=1 
  -n student-management
```
Check:
```bash
kubectl get deployment  student-management-php  -n student-management
```
For a short time you'll see:
But because your deployment is controlled by Argo CD with selfHeal: true, Argo CD detects that Kubernetes has replicas: 1 while GitHub YAML says:
replicas: 2
and automatically restores it to 2 replicas.
That is actually proof that your Argo CD self-healing is working correctly. ✅
Verify it
Run:
```bash
kubectl get applications -n argocd
```
Then:
```bash
kubectl get deployment student-management-php -n student-management
```
You should continue seeing:
READY   UP-TO-DATE   AVAILABLE
2/2     2            2
Reason: Git contains replicas: 2, and Argo CD has:
syncPolicy:
  automated:
    prune: true
    selfHeal: true
 

If you really want 1 replica
With GitOps, don't manually scale the deployment. Change the desired state in Git:
```bash
cd ~/student-app-cicd
```
```bash
vim k8s/php-deployment.yaml
```
Change: replicas: 2  to:  replicas: 1
Then:
```bash
git status
```
```bash
git add k8s/php-deployment.yaml
```
```bash
git commit -m "Scale PHP deployment to 1 replica"
```
```bash
git pull --rebase origin main
```
```bash
git push origin main
```


 

 

---


## PART 42 — The final real-time architecture

```text
Your project is now:
                   DEVELOPER
                       │
                    git push
                       │
                       ▼
                     GitHub
                       │
                       ▼
                ┌─────────────┐
                │ EC2 RUNNER  │
                └──────┬──────┘
                       │
           ┌───────────┼────────────┐
           │           │            │
           ▼           ▼            ▼
         Tests      Gitleaks     SonarQube
           │           │            │
           └───────────┼────────────┘
                       │
                     PASS
                       │
                       ▼
                 Docker Build
                   /       \
                  ▼         ▼
                PHP       Python
                  \         /
                   ▼       ▼
                  Docker Hub
                       │
                 Git SHA image
                       │
                       ▼
                 Update K8s YAML
                       │
                       ▼
                     GitHub
                       │
              ─────── CD ───────
                       │
                       ▼
                    Argo CD
                       │
              automated sync
              prune + selfHeal
                       │
                       ▼
                    AWS EKS
                 /      |      \
                ▼       ▼       ▼
              PHP     Python   MySQL
              Pods     Pods     Pod
                \       |       /
                 \      |      /
                  ▼     ▼     ▼
                  APPLICATION
```
This is the version I'd recommend you implement first. Do not try to add Jenkins, Terraform, Helm, Prometheus, Grafana, ALB Ingress, RDS, Nexus, Ansible, etc. yet. Get this pipeline working from git push all the way to automatic EKS deployment first. After that, we can upgrade the same project one component at a time.
Continue with the same deployment
•	Fix the Kubernetes manifests
•	Create the GitHub Actions workflow

Prometheus + Grafana + Alertmanager stack inside EKS using Helm


## PART 43 — Monitoring Architecture


 
```text
Prometheus   = Collect metrics
Grafana      = Display metrics/dashboard
Alertmanager = Manage and send alerts
```


## PART 44 — Check Cluster Before Monitoring Installation

Run:
```bash
kubectl get nodes
```
Then:
```bash
kubectl get pods -n student-management
```
Make sure your nodes are Ready and application pods are Running.
Also check available resources:
```bash
kubectl top nodes
```
If this gives:
error: Metrics API not available
don't worry yet. We can handle that separately.

---


## PART 45 — Install Helm

Check first:
```bash
helm version
```
If you get:
helm: command not found
install Helm:
```bash
curl https://raw.githubusercontent.com/helm/helm/main/scripts/get-helm-3 | bash
```
Check again:
```bash
helm version
```
You should see Helm version information.

---


## PART 46 — Add Prometheus Helm Repository

Run:
```bash
helm repo add prometheus-community \
https://prometheus-community.github.io/helm-charts
```
Then:
```bash
helm repo update
```
Check:
```bash
helm repo list
```
You should see:
```text
prometheus-community
```

---


## PART 47 — Create Monitoring Namespace

Create a separate namespace:
```bash
kubectl create namespace monitoring
```
Check:
```bash
kubectl get namespaces
```
```text
You should now have:
student-management
argocd
monitoring
Keeping monitoring separate makes administration easier:
student-management
   └── Application

argocd
   └── GitOps

monitoring
   ├── Prometheus
   ├── Grafana
   └── Alertmanager
```

---


## PART 48 — Install Prometheus + Grafana + Alertmanager

We will use:
kube-prometheus-stack
It installs the major monitoring components together.
Run:
```bash
helm install kube-prometheus-stack \
prometheus-community/kube-prometheus-stack \
--namespace monitoring
```
Wait a little and check:
```bash
kubectl get pods -n monitoring
```
You should eventually see components similar to:
alertmanager-kube-prometheus-stack-alertmanager-0
kube-prometheus-stack-grafana-xxxxx
kube-prometheus-stack-kube-state-metrics-xxxxx
kube-prometheus-stack-operator-xxxxx
prometheus-kube-prometheus-stack-prometheus-0
kube-prometheus-stack-prometheus-node-exporter-xxxxx
Wait until the important pods show:
```text
Running
```
Check everything:
```bash
kubectl get all -n monitoring
```

---


## PART 49 — Verify Prometheus

Check the Prometheus service:
```bash
kubectl get svc -n monitoring
```
Look for:
kube-prometheus-stack-prometheus
For beginner testing, use port-forwarding:
```bash
kubectl port-forward \
-n monitoring \
svc/kube-prometheus-stack-prometheus \
9090:9090 \
--address=0.0.0.0
```
Keep this terminal running.
In the EC2 Security Group temporarily allow:
Type:   Custom TCP
Port:   9090
Source: My IP
Open:
http://EC2-PUBLIC-IP:9090
You should see the Prometheus UI.
After testing, press:
Ctrl+C
and remove the temporary 9090 Security Group rule.

---


## PART 50 — Verify Grafana

Check:
```bash
kubectl get svc -n monitoring
```
Look for:
kube-prometheus-stack-grafana
Start:
```bash
kubectl port-forward \
-n monitoring \
svc/kube-prometheus-stack-grafana \
3000:80 \
--address=0.0.0.0
```
Keep this terminal running.
Temporarily allow EC2 Security Group:
Type:   Custom TCP
Port:   3000
Source: My IP
Open:
http://EC2-PUBLIC-IP:3000

---


## PART 51 — Get Grafana Password

Open another SSH terminal and run:
```bash
kubectl get secret \
kube-prometheus-stack-grafana \
-n monitoring \
-o jsonpath="{.data.admin-password}" \
| base64 --decode; echo
```
Grafana username:
```text
admin
```
Password:
<output from above command>
Log in to Grafana.

---


## PART 52 — Check Grafana Dashboards

After logging in, go to:
Dashboards
The stack automatically installs Kubernetes monitoring dashboards.
```text
You can monitor things such as:
EKS Cluster
     │
     ├── Nodes
     │    ├── CPU
     │    ├── Memory
     │    └── Network
     │
     ├── Namespaces
     │
     ├── Deployments
     │
     └── Pods
          ├── CPU
          ├── Memory
          ├── Restarts
          └── Status
```
Search the Grafana dashboards for Kubernetes/node/pod dashboards.

---


## PART 53 — Verify Prometheus Targets

Open Prometheus and go to:
Status
→ Target health
You should see many targets with:
```text
UP
```
```text
Prometheus will collect Kubernetes infrastructure metrics through components including:
Prometheus
├── Kubernetes API
├── kube-state-metrics
├── node-exporter
└── Kubernetes components
```

---


## PART 54 — Test Prometheus with PromQL

PromQL is the query language used by Prometheus to check Kubernetes metrics.

Temporarily Add inbound rules in EC2 Security Group: **Type: Custom TCP Port: 9090 Source: My IP**

### Step 1 — Start Prometheus Port Forward

On EC2:
```bash
kubectl port-forward \
-n monitoring \
svc/kube-prometheus-stack-prometheus \
9090:9090 \
--address=0.0.0.0
```
Expected:
Forwarding from 0.0.0.0:9090 -> 9090
Keep this terminal running.
Open:
http://<EC2-PUBLIC-IP>:9090

---


### Step 2 — Check Whether Prometheus Targets Are UP

In Prometheus, enter:
```text
up
```
Click Execute.
Expected:
```text
1
```
Meaning:
1 = Target is UP
0 = Target is DOWN

---


### Step 3 — Check Kubernetes Pod Metrics

Run:
```text
kube_pod_status_phase
```
This displays pod status information from Kubernetes.

---


### Step 4 — Check Only Our Application

Run:
```text
kube_pod_status_phase{namespace="student-management"}
```
This filters the results to our application namespace.
```text
We should see metrics for:
student-management
│
├── PHP Pods
├── Python Pods
└── MySQL Pod
```
If results appear, Prometheus is monitoring our application namespace.

---


### Step 5 — Check CPU Usage

Run:
```text
rate(
  container_cpu_usage_seconds_total{
    namespace="student-management",
    container!=""
  }[5m]
)
```
This shows CPU usage for our application containers.

---


### Step 6 — Check Memory Usage

Run:
```text
container_memory_working_set_bytes{
  namespace="student-management",
  container!=""
}
```
This shows memory consumption for our application containers.

---


## PART 54 — Verification

```text
These queries should return results:
up                                                ✓

kube_pod_status_phase                             ✓

kube_pod_status_phase{
  namespace="student-management"
}                                                 ✓

CPU query                                         ✓

Memory query                                      ✓
```
If all return data:

## PART 54 COMPLETE

Prometheus is monitoring Kubernetes successfully.

---


## PART 55 — Verify Alertmanager

```text
Alertmanager handles alerts generated by Prometheus.
Flow:
Problem occurs  --> Prometheus detects it  --> Prometheus Alert Rule  --> Alertmanager  --> Notification  --> Example: -->PHP Pod Down --> Prometheus detects it  --> Alert becomes FIRING  --> Alertmanager receives it
```

### Step 1 — Check Alertmanager Pod



Run:
```bash
kubectl get pods -n monitoring | grep alertmanager
```
Expected similar output:
alertmanager-kube-prometheus-stack-alertmanager-0   2/2   Running
Important:
STATUS = Running

---


### Step 2 — Check Alertmanager Service

Run:
```bash
kubectl get svc -n monitoring | grep alertmanager
```
You should see:
kube-prometheus-stack-alertmanager

---


### Step 3 — Start Alertmanager Port Forward

Run:
```bash
kubectl port-forward \
-n monitoring \
svc/kube-prometheus-stack-alertmanager \
9093:9093 \
--address=0.0.0.0
```
Expected:
Forwarding from 0.0.0.0:9093 -> 9093
Keep this terminal running.

---


### Step 4 — Allow Port 9093 Temporarily

AWS Console: EC2  → Instances → studentserver → Security→ Security Group → Edit inbound rules -->Add:
Type:        Custom TCP
Port:        9093
Source:      My IP
Description: Alertmanager temporary access
Save the rule.

---


### Step 5 — Open Alertmanager

Browser:
http://<EC2-PUBLIC-IP>:9093
You should see the Alertmanager UI.
It is okay if there are currently no application alerts.
We are only verifying that Alertmanager is working.

---


### Step 6 — After Testing

Stop the port-forward:
Ctrl+C
Remove the temporary Security Group rule:
TCP 9093

---


## PART 56 — Verify Complete Monitoring Stack

Run:
```bash
kubectl get pods -n monitoring
```
```text
We should have:
Prometheus        Running
Grafana           Running
Alertmanager      Running
Node Exporter     Running
kube-state-metrics Running
Prometheus Operator Running
Check services:
kubectl get svc -n monitoring
Check Helm:
helm list -n monitoring
You should see:
kube-prometheus-stack
```

---


## PART 56 — Monitoring Architecture

```text
Our monitoring works like this:
                    AWS EKS
                       │
          ┌────────────┼────────────┐
          │            │            │
          ▼            ▼            ▼
      PHP Pods     Python Pods   MySQL Pod
          │            │            │
          └────────────┼────────────┘
                       │
                       ▼
                  PROMETHEUS
                       │
                 Collect Metrics
                       │
            ┌──────────┴──────────┐
            │                     │
            ▼                     ▼
         GRAFANA              ALERTMANAGER
            │                     │
            ▼                     ▼
       Dashboards               Alerts
Simple explanation:
Prometheus
   ↓
Collects metrics
CPU, Memory, Pod Status, etc.

Grafana
   ↓
Displays Prometheus data
as dashboards and graphs

Alertmanager
   ↓
Handles alerts generated
by Prometheus
So remember:
Prometheus   = Collect Metrics

Grafana      = Show Metrics

Alertmanager = Handle Alerts
```

---


## PART 57 — Monitor Student Management Application

Now we move from general Kubernetes monitoring to our actual application monitoring.
```text
We want to monitor:
student-management
│
├── PHP
│   ├── Pod Status
│   ├── CPU
│   ├── Memory
│   └── Restarts
│
├── Python
│   ├── Pod Status
│   ├── CPU
│   ├── Memory
│   └── Restarts
│
└── MySQL
    ├── Pod Status
    ├── CPU
    ├── Memory
    └── Restarts
```
First check our pods:
```bash
kubectl get pods -n student-management
```
All should be:
```text
Running
```
Then in Prometheus run:
```text
kube_pod_info{namespace="student-management"}
```
This confirms Prometheus knows about our application pods.
Then check running pods:
```text
kube_pod_status_phase{
  namespace="student-management",
  phase="Running"
}
```

---


## PART 58 — Create Student Management Grafana Dashboard

After PART 57, we will create our own Grafana dashboard.
```text
Our dashboard will contain:
       STUDENT MANAGEMENT MONITORING
                    │
       ┌────────────┼─────────────┐
       │            │             │
       ▼            ▼             ▼
      PHP         Python        MySQL
       │            │             │
       ├─ CPU       ├─ CPU        ├─ CPU
       ├─ Memory    ├─ Memory     ├─ Memory
       ├─ Status    ├─ Status     ├─ Status
       └─ Restarts  └─ Restarts   └─ Restarts
```



Yes. Based on everything we actually did in your project, below is the complete Loki logging procedure from start to finish, including the problems we encountered, the final working configuration, Alloy, Grafana, LogQL, and dashboard creation.
This is specifically for your current architecture:
AWS EKS: student-cluster
Region: eu-north-1

Application namespace:
```text
student-management
```

Monitoring namespace:
```text
monitoring
```

Logging namespace:
```text
logging
```

```text
Application
 ├── PHP
 ├── Python
 └── MySQL
        │
        ▼
   Grafana Alloy
        │
        ▼
      Loki
        │
        ▼
     Grafana
```

## PART 1 — Verify EKS Connection

Run everything from your Ubuntu EC2 studentserver.
Go to the project:
```bash
cd ~/student-app-cicd
```
Confirm Kubernetes connectivity:
```bash
kubectl get nodes
```
You should have your two EKS nodes in Ready state.
Check your application:
```bash
kubectl get pods -n student-management
```
In your working setup, MySQL, PHP and both Python replicas were running. Pasted text

---


## PART 2 — Create Logging Namespace

Check namespaces:
```bash
kubectl get namespaces
```
If logging does not exist:
```bash
kubectl create namespace logging
```
Verify:
```bash
kubectl get namespace logging
```

---


## PART 3 — Add Grafana Helm Repository

Add the repository:
```bash
helm repo add grafana https://grafana.github.io/helm-charts
```
Update:
```bash
helm repo update
```
Check:
```bash
helm repo list
```
In our setup we ultimately had the Grafana chart repository available for Alloy.
```bash
helm search repo grafana/loki
```


---


## PART 4 — Create Loki Configuration

Create the directory if necessary:
```bash
mkdir -p logging
```
Open:
```bash
vim logging/loki-values.yaml
```
Press:
i
Paste the final configuration we used:
```yaml
deploymentMode: SingleBinary

loki:
  auth_enabled: false

  commonConfig:
    replication_factor: 1

  schemaConfig:
    configs:
      - from: "2024-04-01"
        store: tsdb
        object_store: filesystem
        schema: v13
        index:
          prefix: loki_index_
          period: 24h

  storage:
    type: filesystem

  pattern_ingester:
    enabled: true

  limits_config:
    allow_structured_metadata: true
    volume_enabled: true

  rulerConfig:
    enable_api: true

minio:
  enabled: false

singleBinary:
  replicas: 1

  persistence:
    enabled: false

chunksCache:
  enabled: false

resultsCache:
  enabled: false

backend:
  replicas: 0

read:
  replicas: 0

write:
  replicas: 0

ingester:
  replicas: 0

querier:
  replicas: 0

queryFrontend:
  replicas: 0

queryScheduler:
  replicas: 0

distributor:
  replicas: 0

compactor:
  replicas: 0

indexGateway:
  replicas: 0

bloomPlanner:
  replicas: 0

bloomBuilder:
  replicas: 0

bloomGateway:
  replicas: 0
```
Save Esc , :wq ,Enter key

---


## PART 5 — Why We Used This Loki Configuration

Initially we encountered two major problems.
 
The MinIO image failed with:
```text
401 Unauthorized
and Loki's cache requested too much memory—approximately:
9830Mi
which caused:
Insufficient memory
Therefore, for this learning project we simplified Loki to:
SingleBinary Loki
+
filesystem storage
+
no MinIO
+
no chunks cache
+
no results cache
This is much more appropriate for your small two-node learning cluster.
Important limitation
We deliberately configured:
persistence:
  enabled: false
Therefore /var/loki is ephemeral.
This means:
Loki pod deleted/recreated
        ↓
Old Loki log data may be lost
This setup is good for learning/testing, not the final production architecture.
For production we would normally move Loki storage to durable object storage such as S3.

Because this is a small learning cluster, I deployed Loki in SingleBinary mode. For a production environment, I would use a scalable Loki architecture with durable object storage such as Amazon S3.
```

### STEP 6 — Validate the YAML

Before installation:
```bash
grep -E "deploymentMode:|enabled: false|replicas:" logging/loki-values.yaml
```
You should see approximately:
deploymentMode: SingleBinary
  auth_enabled: false
  enabled: false
  replicas: 1
    enabled: false
  enabled: false
  enabled: false
  replicas: 0
  replicas: 0
  ...
Your actual run produced exactly this pattern: SingleBinary, one replica, and the other deployment components at zero replicas. Pasted text
The multiple:
replicas: 0
entries are correct, not errors.




---


## PART 6 — Install Loki


### STEP 7 — Install Loki

Use this corrected command consistently:
```bash
helm upgrade --install loki grafana/loki \
  -n logging \
  -f logging/loki-values.yaml
```
Using upgrade --install is useful because the same command handles both:
Loki doesn't exist → Install

Loki already exists → Upgrade
Your later history also records this as the reinstall command. Pasted text
Check:
```bash
helm list -n logging
```
Your successful installation showed Loki deployed with chart loki-18.13.8, app version 3.7.8.
Check pods:
```bash
kubectl get pods -n logging
```
We eventually had:
loki-0                          2/2 Running
loki-canary-...                 1/1 Running
loki-canary-...                 1/1 Running
loki-gateway-...                2/2 Running

---


## PART 7 — Check Loki Services

Run:
```bash
kubectl get svc -n logging
```
We had services including:
loki
loki-canary
loki-gateway
loki-gateway-exporter
loki-headless
loki-memberlist
The important service for our architecture is:
loki-gateway
Alloy and Grafana communicate with Loki through this gateway.

---


## PART 8 — Test Loki Health

We used port forwarding for command-line testing:
```bash
kubectl port-forward -n logging svc/loki 3100:3100
```
At one point you got:
bind: address already in use
because port 3100 was already being forwarded.
We confirmed Loki was available using:
Keep that terminal running.
From another SSH terminal:

```bash
curl http://localhost:3100/ready
```
and received:
ready

That confirmed:
Loki Pod
   ↓
Service
   ↓
Port forward
   ↓
localhost:3100
   ↓
```text
READY
```

Open Ec2 instance--> security group--> student-app-sg--> edit inbound rules-->
IPv4	Custom TCP	TCP	3100	0.0.0.0/0

You do not need to expose Loki port 3100 publicly through the EC2 Security Group. 

```bash
sudo ss -ltnp | grep :3100
```


---


## PART 9 — Install Grafana Alloy

Now we need something to collect Kubernetes pod logs.
Create:
```bash
vim logging/alloy-values.yaml
```
```yaml
Paste:
alloy:
  configMap:
    content: |
      logging {
        level  = "info"
        format = "logfmt"
      }

      discovery.kubernetes "pods" {
        role = "pod"
      }

      discovery.relabel "pod_logs" {
        targets = discovery.kubernetes.pods.targets

        rule {
          source_labels = ["__meta_kubernetes_namespace"]
          target_label  = "namespace"
        }

        rule {
          source_labels = ["__meta_kubernetes_pod_name"]
          target_label  = "pod"
        }

        rule {
          source_labels = ["__meta_kubernetes_pod_container_name"]
          target_label  = "container"
        }

        rule {
          source_labels = ["__meta_kubernetes_pod_label_app"]
          target_label  = "app"
        }
      }

      loki.source.kubernetes "pods" {
        targets    = discovery.relabel.pod_logs.output
        forward_to = [loki.write.local.receiver]
      }

      loki.write "local" {
        endpoint {
          url = "http://loki-gateway.logging.svc.cluster.local/loki/api/v1/push"
        }
      }
```
Save:
Esc
:wq

---


## PART 10 — Understand the Loki Internal URL/address

```text
The important URL is:
http://loki-gateway.logging.svc.cluster.local
Alloy uses:
http://loki-gateway.logging.svc.cluster.local/loki/api/v1/push


Kubernetes DNS follows:
SERVICE.NAMESPACE.svc.cluster.local
Therefore:
Service:
loki-gateway

Namespace:
logging

        ↓

loki-gateway.logging.svc.cluster.local
Alloy sends logs to:
http://loki-gateway.logging.svc.cluster.local/loki/api/v1/push
```

---


## PART 11 — Install Alloy

Run:
```bash
helm upgrade --install alloy grafana/alloy \
  -f logging/alloy-values.yaml \
  -n logging
```
Your successful installation showed:
alloy   logging   deployed   alloy-1.13.0   v1.20.0
loki    logging   deployed   loki-18.13.8   3.7.8
```bash
helm list -n logging
```



Check:
```bash
kubectl get pods -n logging
```
In your successful installation Alloy ran two pods, one on each node. Pasted text

Eventually both Alloy pods became:
alloy-lsgmm    2/2 Running
alloy-p77nv    2/2 Running

---


## PART 12 — Why There Are Two Alloy Pods Verify Alloy DaemonSet

Run:
```bash
kubectl get daemonset -n logging
```
```text
We confirmed:
NAME    DESIRED CURRENT READY AVAILABLE
alloy   2       2       2     2
Alloy is running as a DaemonSet.
Alloy runs as a DaemonSet. Therefore Kubernetes runs an Alloy pod on each EKS worker node, allowing it to collect logs from workloads across the cluster.
Because your EKS cluster has two worker nodes:
EKS Node 1
   ↓
Alloy Pod
```

EKS Node 2
   ↓
Alloy Pod
This is why there are two Alloy pods.

---


## PART 13 — Correct Way to Check Alloy Logs

That was because Alloy is a DaemonSet, not a Deployment.
The correct command is:
```bash
kubectl logs -n logging daemonset/alloy -c alloy --tail=50
```
You can also inspect individual pods:
```bash
kubectl logs -n logging alloy-lsgmm -c alloy --tail=30
```
and:
```bash
kubectl logs -n logging alloy-p77nv -c alloy --tail=30
```

---


## PART 14 — Verify Alloy Is Collecting Application Logs

This was a major successful checkpoint.
Alloy showed:
opened log stream
That indicates Alloy is reading Kubernetes logs.
for your Python application. Pasted text
It also opened the PHP log stream. Pasted text
That proved:
Application Pods
       ↓
     Alloy
       ↓
logs being collected

---


## PART 15 — Verify Loki Received the Logs

We queried Loki:
```bash
curl -s http://localhost:3100/loki/api/v1/label/namespace/values
```
Your actual response was:
{"status":"success","data":["argocd","kube-system","logging","monitoring","student-management"]}
Pasted text
The critical value was:
```text
student-management
```
Your project produced that namespace in Loki.
student-management Pods
        ↓
      Alloy
        ↓
 Loki Gateway
        ↓
      Loki
        ↓
Logs successfully stored/queryable
 

---


## PART 16 — Add Loki to Grafana

Your Grafana is already accessible from your browser through:
Grafana dashboard
In Grafana go to:
Connections  --> Data sources   --> Add new data source  --> search : Loki  -->select :Loki
Important: On the Add data source screen, search for:
```text
Loki
```
Do not put the Loki URL into the data-source search field.
After selecting Loki, configure:
Name:
```text
Loki
```

URL:
http://loki-gateway.logging.svc.cluster.local

Authentication:
No Authentication
Leave advanced options at their defaults.
Then:
Save & test
You received:
Data source successfully connected.
So:
Grafana   --> Loki Gateway  --> Loki  --> SUCCESS
 

---


## PART 17 — Why Grafana Does Not Use the EC2 IP for Loki

This distinction is important.
You access Grafana through:
Your Computer
      ↓
EC2 Public IP : 3000
      ↓
Grafana
But Grafana accesses Loki internally:
Grafana
      ↓
loki-gateway.logging.svc.cluster.local
      ↓
```text
Loki
```
Therefore Grafana's Loki URL is not:
http://13.50.250.227:3100
and not:
http://localhost:3100
It is:
http://loki-gateway.logging.svc.cluster.local

---


## PART 18 — Test Loki in Grafana Explore

Open:
Explore
Select:
```text
Loki
```
Use Code mode.
To see all application logs:
{namespace="student-management"}
Set time range, for example:
Last 1 hour
or:
```text
Last 6 hours
```
Click:
Run query
This successfully displayed your application logs.
 

---


## PART 19 — PHP Logs

Use:
```text
{namespace="student-management", container="php"}
```
This displays only PHP container logs.

---


## PART 20 — Python Logs

Use:
```text
{namespace="student-management", container="python"}
```
You have two Python replicas, so logs from both pods can appear.

---


## PART 21 — MySQL Logs

Use:
```text
{namespace="student-management", container="mysql"}
```
This displays only MySQL logs.

---


## PART 22 — Search for Errors

All application lines containing error, case-insensitive:
{namespace="student-management"} |~ "(?i)error"
PHP logs containing 404:
```text
{namespace="student-management", container="php"} |= "404"
PHP logs containing 400:
{namespace="student-management", container="php"} |= "400"
Search warnings:
{namespace="student-management"} |~ "(?i)warning"
```

---


## PART 23 — Create Grafana Loki Dashboard

Go to:
Dashboards  --> New  --> New dashboard  --> Add panel  --> Select:
```text
Loki
```
We named the dashboard:
Student Management - Loki Logs

Go Dashboards


Yes. Based on the Grafana screens you showed earlier, here is STEP 19–20 in the exact click-by-click order. This creates the first panel only. After this is saved correctly, you add PHP, Python, and MySQL panels.

### STEP 19 — Create Student Management - Loki Logs

You already added the Loki data source and got Data source successfully connected, so start from the normal Grafana screen.
19.1 Open Dashboards
On the left-side Grafana menu, click:
Dashboards
You will reach the Dashboards page.
Click:
New → New dashboard
You should now see a new empty dashboard.
19.2 Add the first visualization
On the empty dashboard, click:
+ Add visualization
Grafana will ask you to choose a data source.
Select:
```text
Loki
```
Do not choose Prometheus here.
You should now enter the Edit panel screen, similar to the screens you showed previously.

---


### STEP 20 — Configure the first panel

This first panel will display all logs from your student-management namespace.
20.1 Check the data source
At the bottom of the panel editor, make sure the data source shows:
```text
Loki
```
20.2 Select Code mode
In the query section you should see Builder / Code.
Click:
Code
```text
You should now have a query box.
```
20.3 Enter the LogQL query
Delete anything already in the query box and enter exactly:
{namespace="student-management"}
So your query area should look approximately like:
```text
A   Loki

Builder   [Code]

{namespace="student-management"}

                         [Run queries]
Then click:
Run queries
20.4 Confirm that logs appear
You should now see log entries in the large preview area.
Based on the logs you showed previously, this query should combine logs from the application containers rather than restricting the result to only PHP or Python.
Conceptually:
{namespace="student-management"}

              ↓

┌─────────────────────────────┐
│ student-management          │
│                             │
│ PHP logs                    │
│ Python logs                 │
│ MySQL logs                  │
└─────────────────────────────┘
```
If you see logs, continue.
If you see No data, don't create the panel yet. First change the Grafana time selector in the upper-right from a short period to:
```text
Last 6 hours
```
and run the query again.

---

20.5 Select the Logs visualization
Look at the right-hand side of the panel editor.
Find the visualization selector.
Choose:
```text
Logs
```
This is important. We are displaying raw application logs, so don't select:
Time series
```text
Stat
```
Gauge
Table
For this first panel use:
Visualization = Logs

---

20.6 Give the panel its title
On the right side, find:
Panel options
Under Title, enter:
Student Management - Application Logs
At this stage you should have exactly:
Data source:
```text
Loki
```

Query:
```text
{namespace="student-management"}

Visualization:
Logs

Panel title:
Student Management - Application Logs
________________________________________
STEP 20.7 — Save the panel/dashboard
Now click Save in the upper-right.
Because this is the first panel of a new dashboard, Grafana may ask you for the dashboard name.
Enter exactly:
Student Management - Loki Logs
Then confirm:
Save
Be careful with these two names:
DASHBOARD NAME
Student Management - Loki Logs

```
        ≠

PANEL NAME
Student Management - Application Logs
The dashboard is the complete page. The panel is only one box inside that page.

---

What you should see after saving
Your screen should now look conceptually like this:
Dashboard:
Student Management - Loki Logs
────────────────────────────────────────────

┌───────────────────────────────────────────┐
│ Student Management - Application Logs    │
```text
│                                           │
│ 10.0.x.x ... GET / ...                   │
│ Python/Flask logs ...                     │
│ PHP/Apache logs ...                       │
│ MySQL logs ...                            │
│                                           │
└───────────────────────────────────────────┘
This matches the dashboard structure you later built in your previous screenshots: the first panel contained all application logs, followed by separate PHP, Python, and MySQL panels. Pasted text
Important: don't create another dashboard for the next panel
Once this is saved, stay inside:
Student Management - Loki Logs
For the next panel you will use:
Add panel → Add visualization → Loki
and create:
PHP Application Logs

Query:
```
{namespace="student-management", container="php"}
That panel must be added to the same dashboard, not another new dashboard.
If you're currently looking at the Grafana screen, complete only the first panel above.
Continue with the same dashboard
•	Add the PHP logs panel
•	Fix a “No data” result




---


## PART 24 — Panel 1: All Application Logs

Data source:
```text
Loki
```
Query:
{namespace="student-management"}
```text
Visualization:
```
```text
Logs
```
Title:
Student Management - Application Logs
Save it.

---


## PART 25 — Panel 2: PHP Application Logs

From the existing dashboard:
Add panel
   ↓
Visualization
   ↓
```text
Loki
```
Query:
```text
{namespace="student-management", container="php"}
```
Visualization:
```text
Logs
```
Title:
PHP Application Logs
Save it to the existing dashboard, not a new dashboard.

---


## PART 26 — Panel 3: Python Application Logs

Query:
```text
{namespace="student-management", container="python"}
```
Visualization:
```text
Logs
```
Title:
Python Application Logs
Save.

---


## PART 27 — Panel 4: MySQL Application Logs

Query:
```text
{namespace="student-management", container="mysql"}
```
Visualization:
```text
Logs
```
Title:
```text
MySQL Application Logs
Save.
Your dashboard now has:
STUDENT MANAGEMENT - LOKI LOGS

┌──────────────────────────────┬──────────────────────────────┐
│ Student Management          │ PHP Application              │
│ Application Logs            │ Logs                         │
├──────────────────────────────┼──────────────────────────────┤
│ Python Application          │ MySQL Application            │
│ Logs                        │ Logs                         │
└──────────────────────────────┴──────────────────────────────┘
```

---


## PART 28 — Panel 5: HTTP 4xx Errors

This is the next step we were working on.
Add another panel:
Add panel
→ Visualization
→ Loki
Use:
sum(
```text
  count_over_time(
    {namespace="student-management", container="php"}
    |~ "\" 4[0-9][0-9] "
    [$__auto]
  )
)
Visualization:
```
```text
Stat
Title:
HTTP 4xx Errors
This counts HTTP client errors such as:
400
401
403
404
```

---


## PART 29 — Panel 6: HTTP 5xx Errors

After the 4xx panel, create another Stat panel.
Use:
sum(
  count_over_time(
    {namespace="student-management", container="php"}
    |~ "\" 5[0-9][0-9] "
```text
    [$__auto]
  )
)
Visualization:
Stat
Title:
HTTP 5xx Errors
```
This is particularly useful because:
4xx
→ client/request problem

5xx
→ server/application problem

---


## PART 30 — Useful LogQL Cheat Sheet

All project logs:
```text
{namespace="student-management"}
PHP:
{namespace="student-management", container="php"}
Python:
{namespace="student-management", container="python"}
MySQL:
{namespace="student-management", container="mysql"}
Errors:
{namespace="student-management"} |~ "(?i)error"
Warnings:
{namespace="student-management"} |~ "(?i)warning"
404:
{namespace="student-management", container="php"} |= "404"
4xx count:
sum(count_over_time({namespace="student-management", container="php"} |~ "\" 4[0-9][0-9] " [$__auto]))
5xx count:
sum(count_over_time({namespace="student-management", container="php"} |~ "\" 5[0-9][0-9] " [$__auto]))
```

---


## PART 31 — Final Architecture

```text
You have now implemented:
                    INTERNET
                       │
                       ▼
              Student Application
                       │
             ┌─────────┼─────────┐
             ▼         ▼         ▼
            PHP      Python     MySQL
             │         │         │
             └─────────┼─────────┘
                       │
                 Container Logs
                       │
                       ▼
                Grafana Alloy
                DaemonSet
                1 per EKS node
                       │
                       │ /loki/api/v1/push
                       ▼
                  Loki Gateway
                       │
                       ▼
                      Loki
                 SingleBinary
                       │
                       ▼
                    Grafana
                       │
             ┌─────────┴─────────┐
             ▼                   ▼
           Explore            Dashboard
             │                   │
             ▼                   ▼
           LogQL          Application Logs
                          PHP Logs
                          Python Logs
                          MySQL Logs
                          4xx Errors
                          5xx Errors
The three monitoring technologies in your project
You can now distinguish the roles clearly:
Prometheus
→ collects METRICS
→ CPU, memory, pod metrics, request metrics
→ PromQL

Alertmanager
→ receives and routes ALERTS
→ notifications / alert handling

Loki + Alloy
→ collects LOGS
→ PHP/Python/MySQL/Kubernetes logs
→ LogQL

Grafana
→ visualizes metrics and logs
→ dashboards
One important note for your project documentation: your current Loki storage is intentionally non-persistent. It is suitable for this training project, but a production implementation should use durable storage—typically object storage such as S3—before treating Loki as a long-term logging platform.


```

We have Loki + Alloy in logging, Prometheus/Grafana/Alertmanager in monitoring, our application in student-management, and the EKS cluster student-cluster. Pasted text
If by deletion procedure you mean delete the complete AWS/EKS project and avoid AWS charges, use this order.

### 1. First confirm you are connected to the correct cluster

Run from:
ubuntu@studentserver:~$
or:
```bash
cd ~/student-app-cicd
```
Then:
```bash
kubectl get nodes
```
```bash
kubectl get namespaces
```
```bash
helm list -A
```
Do not start by deleting the EC2 instance.

### 2. Delete Loki + Alloy first

Check:
```bash
helm list -n logging
```
You should see loki and alloy, because these were installed as Helm releases in your logging namespace.
Delete them:
```bash
helm uninstall alloy -n logging
```
```bash
helm uninstall loki -n logging
```
Then:
```bash
kubectl delete namespace logging
```
Verify:
```bash
kubectl get namespace logging
```
Expected:
Error from server (NotFound)

### 3. Delete Prometheus + Grafana + Alertmanager

Check:
```bash
helm list -n monitoring
```
Then:
```bash
helm uninstall kube-prometheus-stack -n monitoring
```
Delete namespace:
```bash
kubectl delete namespace monitoring
```
Verify:
```bash
kubectl get namespace monitoring
```
This removes the monitoring stack you installed with kube-prometheus-stack. Pasted text

### 4. Delete Argo CD

First check:
```bash
kubectl get applications -n argocd
```
Delete your application:
```bash
kubectl delete application student-management -n argocd
```
Then delete Argo CD:
```bash
kubectl delete namespace argocd
```
Check:
```bash
kubectl get namespace argocd
```

### 5. Delete Student Management application

Now:
```bash
kubectl delete namespace student-management
```
Check:
```bash
kubectl get namespace student-management
```
At this point your application, PHP, Python and MySQL Kubernetes resources should be gone.

### 6. IMPORTANT — Delete EKS using Terraform

Your project created the infrastructure using Terraform:
```bash
cd ~/student-app-cicd/terraform
```
First:
```bash
terraform state list
```
Then:
```bash
terraform plan -destroy -var-file="dev.tfvars"
```
Review the output carefully.
If it looks correct:
```bash
terraform destroy -var-file="dev.tfvars"
```
Terraform will ask:
Do you really want to destroy all resources?
Enter:
```text
yes
```
Wait until you get:
Destroy complete!
Because you created the EKS infrastructure with Terraform, use terraform destroy rather than separately running eksctl delete cluster. Mixing deletion methods can leave Terraform state inconsistent.

### 7. Verify AWS resources are gone

Your project is using eu-north-1, so check:
```bash
aws eks list-clusters --region eu-north-1
```
Then check load balancers:
```bash
aws elb describe-load-balancers \
  --region eu-north-1 \
  --query 'LoadBalancerDescriptions[*].LoadBalancerName' \
  --output table
```
Check EBS volumes:
```bash
aws ec2 describe-volumes \
  --region eu-north-1 \
  --query 'Volumes[*].[VolumeId,State,Size,Tags]' \
  --output table
```
Also check:
```bash
aws ec2 describe-nat-gateways \
  --region eu-north-1 \
  --filter Name=state,Values=available,pending \
  --query 'NatGateways[*].[NatGatewayId,State,VpcId]' \
  --output table
```
Pay particular attention to leftover Load Balancers, EBS volumes and NAT Gateways, because these can continue generating charges.

### 8. Delete SonarQube from EC2

If you're going to delete the EC2 instance anyway, this isn't strictly necessary. But for a clean teardown:
```bash
docker stop sonarqube
```
```bash
docker rm sonarqube
```
```bash
docker volume rm sonarqube_data
```
Check:
```bash
docker ps -a
```
```bash
docker volume ls
```
### 9. Remove GitHub self-hosted runner

Before terminating EC2, go to your GitHub repository:
Settings → Actions → Runners
Remove:
studentserver-runner
You don't need to delete your GitHub repository unless you actually want to remove the project/source code.

### 10. Finally terminate the EC2 studentserver

Only after Terraform destruction has completed successfully:
AWS Console → EC2 → Instances → studentserver → Instance state → Terminate instance
Then check your AWS console for remaining resources.
```text
The safe deletion sequence is therefore:
Loki + Alloy
     ↓
Monitoring
     ↓
Argo CD
     ↓
Student Application
     ↓
Terraform Destroy
     ↓
Verify EKS / ELB / EBS / NAT
     ↓
Remove GitHub Runner
     ↓
Terminate studentserver EC2
Do not delete the EC2 studentserver first, because you're currently using it to administer Terraform, kubectl, Helm, AWS CLI, and the EKS cluster.
```
Useful checks before teardown
•	Create a deletion checklist
•	Add rollback and safety checks


