<div align="center">

<img src="img/storage.png" alt="WMS Logo" width="120" />

<br/>
<br/>

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
- Register with role selection
- Login with username or email
- Security question for password recovery
- Session-based authentication with role checks

![Register Flow](img/preview-register.gif)

### 🎠 3D Role Selection
When signing up, users pick their role through an interactive 3D carousel — cards rotate in a circular ring, flip to reveal details, and confirm the selection.

![Role Carousel](img/preview-carousel.gif)

### 🛒 Customer Experience
- View available inventory with live stock counts
- Place orders by quantity
- Track order status (pending → approved → processing → shipped → delivered)
- Submit feedback with star rating

![Customer Order](img/preview-order.gif)

### 📊 Manager Dashboard
- Inventory health analytics with circular progress charts
- Add / edit / delete inventory items
- Approve or reject customer orders
- View customer feedback
- Evaluate employee performance

![Manager Dashboard](img/preview-manager.gif)

### 📦 Supplier Interface
- View current stock levels
- Restock low-inventory items
- Submit supply reports

### 🎨 Modern UI
- Dark theme with blue → purple gradient accents
- Responsive design (mobile + desktop)
- Smooth transitions and hover effects
- Consistent design language across all pages

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8 (procedural + MySQLi) |
| Database | MySQL |
| Frontend | HTML5, CSS3, JavaScript |
| UI Framework | Bootstrap 5 |
| Icons | Bootstrap Icons, Font Awesome |

## 📂 Project Structure


 


