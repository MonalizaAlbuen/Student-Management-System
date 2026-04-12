# 📚 Student Management System

A web-based **Student Management System** built using **PHP and MySQL** that manages students, teachers, classes, attendance, exams, and library transactions.
This system is integrated with a **Library Management System via API**, allowing both systems to share student data and track issued books per student.

---

## 🚀 Features

- 🔐 User Authentication (Admin / Teacher / Student / Parent)
- 👨‍🎓 Student Management (Add, Update, Delete)
- 👩‍🏫 Teacher Management
- 📅 Class Scheduling System
- 📝 Exam and Results Management
- 📊 Attendance Tracking System
- 📢 Notice Board System
- 📚 Library Book Issuing System (Integrated)
- 🔄 API-based system integration

---

## 🔗 System Integration (API)

This system is integrated with the **Library Management System** using a custom-built API.

### 📌 Purpose of Integration
The API connects both systems and allows **students to be linked with issued books**, ensuring accurate tracking of borrowed books per student.

### 📌 Key Features
- 📚 Each issued book is linked to a specific student record  
- 🔄 Real-time data exchange between Student and Library systems  
- 🧾 Tracks borrowing history per student  
- ⚙️ Reduces data duplication across systems  
- 🧩 Enables modular and scalable architecture  

### 📡 API Endpoint

GET /api/get-students.php
Returns student data used by the Library System for book issuance and tracking.

---

## 🛠️ Tech Stack

- PHP (Core)
- MySQL Database
- HTML, CSS, JavaScript
- Bootstrap (UI styling)

---

## 📂 Project Structure

student-management-system/
│
├── api/
│ └── get-students.php
│
├── assets/
├── images/
├── database/
│ └── student-management-system.sql
│
├── includes/
│ ├── header.php
│ ├── footer.php
│ ├── sidebar.php
│ └── nav-menu.php
│
├── modules/
│ ├── student.php
│ ├── teacher.php
│ ├── attendance.php
│ ├── exam.php
│ ├── schedule.php
│ ├── notice.php
│ └── class.php
│
├── auth/
│ ├── login.php
│ └── logout.php
│
├── index.php
├── profile.php
├── database.php
├── README.md
└── LICENSE


---

## ⚙️ Installation

```bash
git clone https://github.com/YOUR_USERNAME/student-management-system.git

Setup Steps:
Import the database file (library_db.sql) into MySQL
Move project folder to htdocs (XAMPP)
Start Apache and MySQL
Open in browser:
http://localhost/student-management-system/

📡 API Endpoints Used

👨‍🎓 Get Students
http://localhost/Student-Management-System/api/get_students.php

👨‍💻 Author

NAME: Albuen, Monaliza F.
GitHub: https://github.com/MonalizaAlbuen/Student-Management-System.git



Use the following credentials to access the system after installation:
👤 Username: teacher@gmail.com
🔒 Password: teacher123
⚠️ These credentials are provided for demonstration purposes. It is recommended to change the password after installation.







