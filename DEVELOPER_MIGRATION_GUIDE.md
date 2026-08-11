# Findownn — Developer Migration & Setup Guide

Welcome to the **Findownn Sports Booking Platform** codebase! This document provides complete instructions for developers joining the project or migrating the application and MySQL database to a new server or local development machine.

---

## 🚀 Quick Start (Automated Setup)

If you are on Windows with **PowerShell** and **XAMPP / WAMP / Laragon**, run the automated setup script:

```powershell
.\DEVELOPER_SETUP.ps1
```

The script will automatically:
1. Create the `findownn_admin` database in MySQL.
2. Import the complete master schema & seed data from `findownn_database_master.sql`.
3. Create all required runtime storage, log, and image upload directories.

---

## 🛠️ System Requirements & Stack Overview

- **Language / Runtime**: PHP 8.1 or higher (PDO extension, GD extension, cURL, MBString enabled).
- **Database**: MySQL 5.7+ / MySQL 8.0+ or MariaDB 10.4+.
- **Web Server**: Apache HTTP Server (with `mod_rewrite` enabled) or Nginx.
- **Frontend Technologies**: HTML5, CSS3 (Vanilla Dark Glassmorphic Theme), Bootstrap 5.3, JavaScript (ES6+).
- **Backend Architecture**: MVC Architecture (Admin & API), PDO Database Abstraction layer.

---

## 📂 Project Directory Structure

```
findownn_website/
├── admin/                      # Admin Panel (MVC Architecture)
│   ├── app/
│   │   ├── Controllers/        # Controllers (Venue, Booking, User, Image, API, etc.)
│   │   ├── Core/               # Framework Core (Router, Database, Model, Config)
│   │   ├── Middleware/         # Auth, CSRF, Admin Middleware
│   │   └── Models/             # Eloquent-style PDO Data Models
│   ├── config/                 # Database & App Config (database.php)
│   ├── database/migrations/    # Individual SQL migration scripts (001 to 007)
│   ├── storage/                # Backups, logs, cache
│   └── views/                  # PHP Blade-style View Templates
├── api/v1/                     # Mobile API Endpoints (CourtController, BookingController)
├── assets/                     # Public web assets (Images, CSS, JS)
├── includes/                   # Common frontend includes (Header, Footer, DB connection)
├── DEVELOPER_SETUP.ps1          # 1-Click PowerShell Developer Setup Script
├── DEVELOPER_MIGRATION_GUIDE.md# This Developer Migration Guide
└── findownn_database_master.sql# Complete Master SQL Database Dump (24 Tables)
```

---

## 💾 Manual Database Migration & Import

If you prefer to import the database manually:

### Option A: Via phpMyAdmin
1. Open phpMyAdmin (`http://localhost/phpmyadmin/`).
2. Create a new database named **`findownn_admin`** (Collation: `utf8mb4_unicode_ci`).
3. Select `findownn_admin` and click **Import**.
4. Choose the file **`findownn_database_master.sql`** from the root folder.
5. Click **Import**.

### Option B: Via MySQL CLI Command Line
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS findownn_admin DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p findownn_admin < findownn_database_master.sql
```

---

## ⚙️ Environment & Database Configuration

Database configuration settings are stored in two key locations:

1. **Admin & API Core Configuration**: `admin/app/Core/Config.php` & `admin/config/database.php`
```php
'db' => [
    'host' => 'localhost',
    'database' => 'findownn_admin',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
]
```

2. **Frontend Website Database Connection**: `includes/db.php`
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'findownn_admin');
define('DB_USER', 'root');
define('DB_PASS', '');
```

---

## 🔑 Initial Admin & User Login Credentials

The master database contains pre-configured users for development & testing:

| Role | Email | Password | Phone |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `superadmin@findownn.com` | `password` | `+91 99999 00001` |
| **Admin** | `admin@findownn.com` | `password` | `+91 99999 00002` |
| **Venue Owner** | `rahul@venue.com` | `password` | `+91 98765 43210` |

> *Note: User passwords are stored as bcrypt hashes in the `users` table.*

---

## 🌐 Application URLs & Endpoints

| Environment Component | Local URL |
| :--- | :--- |
| **Public Web Portal** | `http://localhost/findownn_website/` |
| **Admin Dashboard** | `http://localhost/findownn_website/admin/` |
| **Mobile REST API (v1)** | `http://localhost/findownn_website/api/v1/` |

---

## 📊 Database Schema Summary (24 Tables)

- **`users`**: Platform accounts (Super Admin, Admin, Venue Owners).
- **`venues`**: Registered Box Cricket turfs and Pickleball court venues in Bhuj.
- **`courts`**: Individual play courts/fields under each venue.
- **`court_images` & `venue_images`**: Multi-image galleries and featured photos.
- **`sports`**: Supported sports categories (Box Cricket, Pickleball, Football, Badminton).
- **`bookings`**: Player slot reservations, start/end times, and statuses.
- **`payments`**: Payment transactions and invoice billing logs.
- **`cities` & `states`**: Geographical locations (Bhuj, Gandhidham, Gujarat, etc.).
- **`settings`**: Dynamic platform config settings.
- **`audit_logs` & `activity_logs`**: System audit trail logging all admin actions.
- **`whatsapp_messages` & `whatsapp_templates`**: Automated WhatsApp notification templates.

---

## 💡 Support & Incremental Migrations

Future database structural changes should be added as incremental SQL migration files in `admin/database/migrations/` using sequential prefix numbering (e.g., `008_new_feature.sql`).
