<h1 align="center" style="color:#FF4444;">🩸 BloodStock Management System</h1>

<p align="center">
  <img src="https://readme-typing-svg.demolab.com/?font=Fira+Code&weight=500&size=22&pause=1000&color=FF4444&center=true&vCenter=true&width=600&lines=Blood+Stock+Management+System;Auto+Expiry+%7C+Hospital+Request+Simulator;PHP+%2B+MySQL+%2B+AJAX+%3D+%F0%9F%A9%B8" alt="Typing Animation" />
</p>

<p align="center">
  <img src="https://img.shields.io/badge/🚑%20Status-Completed-ff0033?style=for-the-badge&logo=dropbox&logoColor=white" />
  <img src="https://img.shields.io/badge/💉%20Database-MySQL-990000?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/🧠%20Backend-PHP-cc0000?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/🖥️%20Frontend-HTML%2FCSS%2FJS-d42a2a?style=for-the-badge&logo=javascript&logoColor=white" />
</p>

---

## 🧠 Overview

**BloodStock Management System (BSMS)** is a locally hosted web application built with PHP and MySQL. It simulates the real-time management of blood inventory with features like donor history, hospital blood requests, stock threshold alerts, expiry tracking, and simulated donor notifications — all bundled into a sleek dashboard.

---

## 📸 Screenshots

### 🔐 Login Page
<p align="center">
  <img src="/assets/images/Login.png" alt="Login Page" width="600" />
</p>

---

### 📊 Dashboard Overview
<p align="center">
  <img src="assets/images/Dashboard.png" alt="Dashboard" width="600" />
</p>

---

### 🩸 Blood Inventory Table
<p align="center">
  <img src="assets/images/Inventory.png" alt="Blood Inventory" width="600" />
</p>

---

### 🏥 Hospital Request Simulation
<p align="center">
  <img src="assets/images/Request.png" alt="Request Simulation" width="600" />
</p>

---

## ⚙️ Features

- 🩸 **Add & Track Blood Units:** Input donor details, blood group, expiry date, and quantity.
- 🕒 **Auto-Expiry Check:** System auto-detects and marks expired units.
- 📉 **Low-Stock Alerts:** Configurable thresholds for each blood group.
- 🏥 **Hospital-Side Requests:** Dummy hospital interface to simulate urgent needs.
- 🔔 **Donor Alert Simulation:** Suggests past donors when stocks run low.
- 📈 **Analytics:** Blood group-wise charts, usage logs, and expiry reports.

---

## 💻 Technologies Used

| Layer       | Stack                      |
|-------------|----------------------------|
| Frontend    | HTML, CSS, JavaScript (AJAX) |
| Backend     | PHP                         |
| Database    | MySQL                       |
| Charts      | Chart.js                    |
| Hosting     | Localhost (XAMPP/WAMP)      |

---

## 🛠️ Setup Instructions

git clone https://github.com/Sripramod-Y/Blood-Stock-Management.git
Import SQL:
Open MySQL and import the Bloodstock.sql file.

Set Up Config:
Edit config.php like this:


    $host = 'localhost';
    $username = 'root';
    $password = '';
    $db_name = 'bloodstock';

Run Locally:

Launch Apache & MySQL in XAMPP/WAMP.

Visit http://localhost/BSMS/index.php. (BSMS - Project root folder inside C:/xammp/htdocs/BSMS

🚀 Future Enhancements
📲 Mobile-friendly dark UI

📧 Real email/SMS notifications (Twilio/SMTP)

🔐 Role-based login for hospitals & donors

🔮 AI-based demand prediction

🧪 Project Status
✔️ Core modules working
🧪 Tested with dummy data
📌 Hosted on localhost only

# 📥 Installing Dependencies
📦 Dependencies:
PHPMailer (included in /vendor folder manually)
No Composer required.
And Replace the username and password in contact-donors.php 

<p align="center"> <img src="https://media.giphy.com/media/KzJkzjggfGN5Py6nkT/giphy.gif" width="150px" alt="blood drop gif" /> </p> <p align="center" style="color:#FF4444;"> Built  by <strong>Sripramod</strong><br> CSE @ Anna University | 2025 </p> 
