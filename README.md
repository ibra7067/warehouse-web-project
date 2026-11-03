

 
<div align="center">

<img src="https://github.com/user-attachments/assets/04381a16-08d4-473d-9d98-a21880a6431e" alt="bc-removebg-preview" width="330"   />

$${\color{#38bdf8}\textsf{\textbf{Warehouse}} \space \color{#a855f7}\textsf{\textbf{Training Project}}}$$

<p style="color: #64748b; font-size: 1.1em;">
  A full-stack warehouse management & training platform built with PHP and MySQL
</p>

<p>
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Bootstrap-5-7952B3?style=flat-square&logo=bootstrap&logoColor=white" alt="Bootstrap" />
  <img src="https://img.shields.io/badge/License-Educational-38bdf8?style=flat-square" alt="License" />
</p>

</div>

## 💭 Why I built this

I wanted to understand how a real web application works — not just tutorials, but an actual multi-user system with login, roles, database, and modern UI.

Warehouse management seemed like the perfect subject: it's practical, it has clear user roles (customers, employees, managers, suppliers), and the workflows are real. Instead of building a fake demo, I built something that could actually be used to train warehouse staff.

The goal: **teach warehouse operations through an interactive platform**, not a dry PDF manual.

<img width="800" height="451" alt="download (1)" src="https://github.com/user-attachments/assets/fa4cccf5-08cd-4a85-b9ea-90cc3eef6bfa" />


## 🎯 What it does

WMS is more than a warehouse tracker — it's a **training program**. New staff, students, and suppliers can learn real operational workflows by using the same system that runs the warehouse.

Four roles, four experiences:
- **Customer** — browse inventory, place orders, track deliveries
- **Employee** — process orders, manage stock, track performance
- **Manager** — oversee inventory, approve orders, view analytics
- **Supplier** — restock items, submit reports

Each role sees only what's relevant to them. Same platform, different lenses.

## ✨ Features

### 🔐 Authentication
 <img width="800" height="451" alt="download (4)" src="https://github.com/user-attachments/assets/83002e7a-4d59-4bef-8cc5-5c205f619cc4" />


 

### 🛒 Customer Experience
 <img width="800" height="451" alt="download (3)" src="https://github.com/user-attachments/assets/6fd759ff-3c28-49dc-896c-0ff5c9a3d52c" />


![Customer Order](img/preview-order.gif)

### 📊 Manager Dashboard
 
  <img width="800" height="451" alt="download (2)" src="https://github.com/user-attachments/assets/63c8903a-4bfe-42b0-ac75-ea92e677228f" />

![Manager Dashboard](img/preview-manager.gif)

### 📦 Supplier Interface
- View current stock levels
- Restock low-inventory items
- Submit supply reports
 

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8 (procedural + MySQLi) |
| Database | MySQL |
| Frontend | HTML5, CSS3, JavaScript |
| UI Framework | Bootstrap 5 |
| Icons | Bootstrap Icons, Font Awesome |

## 🚀 How to Run Locally (Using XAMPP)

To run this project on your computer, you need a local web server. The easiest way is to use **XAMPP**, which includes Apache (the server), MySQL (the database), and PHP.

### Step 1: Download and Install XAMPP

1.  Go to the official download page: [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
2.  Download the **XAMPP installer for Windows** (or your operating system).
3.  Run the installer. You can leave all the default settings (it will install to `C:\xampp`).
4.  Once installed, open the **XAMPP Control Panel**.

### Step 2: Start the Server

In the XAMPP Control Panel:
1.  Click **Start** next to **Apache**.
2.  Click **Start** next to **MySQL**.
3.  Both should turn green. If Apache fails to start, another program (like Skype) might be using port 80. Close it and try again.

### Step 3: Download and Place the Project Files

1.  **Download** this project (click the green "Code" button on GitHub -> "Download ZIP").
2.  **Extract** the ZIP file.
3.  Move the extracted folder into the `htdocs` directory inside your XAMPP installation. The path should look like this:
    `C:\xampp\htdocs\warehouse_project`

### Step 4: Set Up the Database

1.  In your browser, go to: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2.  Click on the **"New"** button on the left sidebar.
3.  Create a database named **`mydatabase`** (or any name you prefer) and click **Create**.
4.  Click on the new database, then go to the **"Import"** tab at the top.
5.  Click **"Choose File"** and select the database file provided in the project folder (e.g., `database.sql` or `warehouse.sql`).
6.  Click **"Go"** at the bottom. You should see a success message.

### Step 5: Configure the Project

1.  Go to your project folder: `C:\xampp\htdocs\warehouse_project\include\`
2.  Find the file **`config.database.example.php`**.
3.  **Copy** this file and **paste** it in the same folder, renaming the copy to **`config.php`**.
4.  Open `config.php` in a text editor (like Notepad or VS Code).
5.  Update the database details if necessary. By default, it looks like this:
    ```php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "database.example"; // Make sure this matches the name you created in Step 4


 


