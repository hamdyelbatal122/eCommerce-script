# eCommerce Script — PHP

A fully-featured **eCommerce web application** built with PHP and MySQL. Supports product management, shopping cart, user authentication, and order processing.

## ✨ Features

- 🛍️ Product listing and detail pages
- 🛒 Shopping cart with add/remove/update functionality
- 🔐 User registration and login system
- 📦 Order management and checkout flow
- 🔧 Admin panel for product and order management
- 💾 MySQL database integration

## 🛠️ Tech Stack

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)

## ⚙️ Requirements

- PHP >= 7.0
- MySQL >= 5.6
- Apache/Nginx web server (or XAMPP/WAMP)

## 🚀 Getting Started

1. **Start Apache & MySQL** in XAMPP or your local server

2. **Clone the repository**
   ```bash
   git clone https://github.com/hamdyelbatal122/eCommerce-script.git
   ```

3. **Copy project to web root**
   ```bash
   cp -r eCommerce-script/ /path/to/xampp/htdocs/
   ```

4. **Set up the database**
   - Open `http://localhost/phpmyadmin/`
   - Create a new database (e.g., `ecommerce_db`)
   - Import the SQL file from the `database/` folder

5. **Configure database credentials**
   - Open `config.php` and update:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'ecommerce_db');
   ```

6. **Access the application**
   ```
   http://localhost/eCommerce-script/
   ```

## 📁 Project Structure

```
eCommerce-script/
├── admin/          # Admin panel
├── assets/         # CSS, JS, images
├── database/       # SQL dump file
├── config.php      # Database configuration
├── index.php       # Application entry point
└── README.md
```

## 📄 License

This project is open source and available under the [MIT License](LICENSE).
