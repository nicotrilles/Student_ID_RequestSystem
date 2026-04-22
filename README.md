# Student ID Request System

## Overview

This project is a **full-stack Student ID Request System** built using:

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP
* **Database:** MySQL

It supports both **offline demo mode (localStorage)** and **full backend mode (PHP + MySQL via XAMPP)**.

---

## System Upgrade Summary

This version is a **complete full-stack upgrade** featuring:

* Fully synchronized **Student ↔ Admin system**
* Functional **PHP API backend**
* Integrated **MySQL database**
* Advanced **signature capture system**
* Complete **Admin Dashboard with 3 sections**

---

## Architecture

### Dual Mode System

The system supports two modes:

1. **Backend Mode (Production)**

   * Uses PHP API (`/api/requests.php`)
   * Stores data in MySQL

2. **Fallback Mode (Demo)**

   * Uses `localStorage`
   * Works without XAMPP

This ensures:

* ✅ Works during demos without setup
* ✅ Fully functional with database when deployed

---

## ✍️ Signature Drawing Feature

The request form includes a fully functional signature pad:

* 🖱️ Mouse + 📱 Touch support
* ↩️ Undo (per stroke)
* 🎚️ Pen size slider
* 🔄 Draw / Upload toggle
* 🖼️ Stored as **base64 PNG**

The signature is displayed in the **Admin review modal**.

---

## Student ↔ Admin Synchronization

### Workflow:

**Student Side**

* Submit request → saved to:

  * MySQL (if backend active)
  * OR localStorage (fallback)

**Admin Side**

* Views all requests in real-time
* Approve / Reject updates status instantly

**Tracking**

* Student tracking page reads same data source

---

## Admin Panel Features

### 1. Dashboard

*  Live statistics cards
*  Bar chart visualization
*  Recent activity feed (last 5 actions)
*  Auto-refresh every 30 seconds

---

### 2. Pending Requests

* Shows only **Pending** status
* Badge counter in navigation
* Requests disappear immediately after action

---

### 3. Archives

* Full request history
*  Search + filter
*  Includes rejection notes
*  Non-editable finalized records

---

## Project Structure (Simplified)

```
stirs/
├── admin/
├── student/ index.html / index.php
├── api/
├── config/
├── photo/
```

---

##  Setup Instructions (XAMPP)

### 1. Move Project

Place the project folder inside:

```
C:\xampp\htdocs\stirs
```

---

### 2. Start XAMPP

* Start **Apache**
* Start **MySQL**

---

### 3. Setup Database

Open in browser:

```
http://localhost/stirs/api/setup.php
```

This will:

* Create the database
* Initialize tables
* Seed admin account

---

### 4. Access System

#### Admin Login:

```
http://localhost/stirs/admin/adminLogin.html
```

**Credentials:**

```
Username: admin
Password: admin123
```

---

## Key Features

*  Full CRUD request system
*  Admin approval workflow
*  Signature capture system
*  Real-time synchronization
*  Works with or without backend
*  Responsive design

---

## Notes

* Uses **localStorage fallback** for demo environments
* Designed for **academic / capstone projects**
* Easily extendable (email, notifications, etc.)

---

##  Future Improvements

* Email notifications
* File upload system
* Role-based authentication
* Deployment to live server

---

## Vibe coder programmer
Developed by **nicotrilles**

---
