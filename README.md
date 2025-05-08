# 📚 lms-SorSU-BC

A custom Learning Management System built for **Sorsogon State University – Bulan Campus**.  
This platform is designed to enhance digital learning by enabling instructors and students to manage courses, submit assignments, track performance, and collaborate in a secure, academic environment.

---

## 🚀 Features

### 👤 User Roles
- Admin, Instructor, and Student accounts
- Role-based dashboards and permissions

### 🗂 Course Management
- Create, edit, and delete courses
- Assign students and instructors
- Upload class materials

### 📝 Assignments & Quizzes
- Instructor-managed assignments
- Student file uploads
- Grading system with feedback

### 📊 Grade Monitoring
- Grade input by instructors
- Student access to grades and progress

### 📣 Announcements & Communication
- Post announcements to enrolled users
- Optional messaging or notification system

### 🔐 Authentication & Access Control
- Login system with role distinction
- Secure session handling

---

## 🛠 Tech Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP 
- **Database**: MySQL

---

## ⚙️ Installation

### 1. Clone the repository
```bash
git clone https://github.com/crisvin03/lms-SorSU-BC.git
cd lms-SorSU-BC


2. Configure Environment
Set up .env file with your local DB credentials

Create a new MySQL database

3. Install Dependencies (for Laravel)
bash
Copy
Edit
composer install
npm install && npm run dev
4. Run Migrations
bash
Copy
Edit
php artisan migrate
5. Start the Development Server
bash
Copy
Edit
php artisan serve
