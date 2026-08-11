# Findownn Admin Dashboard

Production-ready SaaS Admin Dashboard for the Findownn sports venue booking platform.
Built with Core PHP 8+, MySQL, MVC Architecture, OOP, PSR-4 Autoloading, and RBAC.

---

## Quick Start (XAMPP)

### 1. Create the Database

Open phpMyAdmin → New → Name it `findownn_admin` → Create.

### 2. Configure Environment

Edit `admin/.env`:

```env
DB_DATABASE=findownn_admin
DB_USERNAME=root
DB_PASSWORD=           # your MySQL password
APP_URL=http://localhost/findownn_website/admin
```

### 3. Run Migrations + Seed

Open a terminal in `c:\xampp\htdocs\findownn_website\admin\` and run:

```bash
php migrate fresh:seed
```

This will:
- Drop all existing tables
- Create the full schema (18 tables)
- Seed default users, plans, venues, and sample bookings

### 4. Open the Dashboard

Visit: **http://localhost/findownn_website/admin**

---

## Default Login Credentials

| Role        | Email                         | Password   |
|-------------|-------------------------------|------------|
| Super Admin | superadmin@findownn.com       | Admin@123  |
| Admin       | admin@findownn.com            | Admin@123  |
| Venue Owner | rahul@venue.com               | Admin@123  |

> **Change all passwords immediately in production.**

---

## Folder Structure

```
admin/
├── app/
│   ├── Controllers/        # HTTP controllers
│   ├── Core/               # Router, DB, Config, Session, Logger
│   ├── Helpers/            # Global functions (functions.php)
│   ├── Middleware/         # Auth, CSRF, Role guards
│   ├── Models/             # PDO-based models
│   └── Services/           # (extend here)
├── database/
│   ├── migrations/         # SQL schema files
│   └── seeders/            # DatabaseSeeder.php
├── public/
│   ├── assets/css/         # admin.css
│   ├── assets/js/          # admin.js
│   ├── index.php           # Entry point
│   └── .htaccess           # URL rewriting
├── routes/
│   └── web.php             # All application routes
├── storage/
│   ├── logs/               # Daily log files
│   └── backups/            # DB backup dumps
├── views/
│   ├── auth/               # login.php
│   ├── dashboard/          # index.php, owner.php
│   ├── errors/             # 404.php
│   ├── layouts/            # main.php, auth.php
│   ├── reports/            # index.php, audit-logs.php
│   ├── settings/           # index.php
│   ├── subscriptions/      # index.php, plans.php, etc.
│   ├── users/              # index.php, create/edit/show
│   └── venues/             # index.php, create/edit/show
├── .env                    # Environment config
├── .htaccess               # Redirects to /public
├── composer.json           # PSR-4 autoloading
└── migrate                 # CLI migration tool
```

---

## Migration Commands

```bash
# Run all pending migrations
php migrate

# Drop all tables and re-run from scratch
php migrate fresh

# Drop all tables only
php migrate rollback

# Seed sample data
php migrate db:seed

# Full reset + fresh seed
php migrate fresh:seed
```

---

## Role Permissions

### Super Admin
- Full system access
- Manage admins, venue owners, venues
- Manage subscription plans
- Assign/remove verified badges
- View reports, audit logs
- Database backup
- System settings

### Findownn Admin
- Approve / reject venues
- Assign verified badges
- Manage users (read/update)
- View analytics and subscriptions

### Venue Owner
- Create, edit own venues
- View own bookings
- View subscription status
- Submit venues for review

---

## Security Features

- **CSRF Protection** — token rotated on every POST
- **XSS Protection** — all output escaped via `e()`
- **SQL Injection** — 100% PDO prepared statements
- **Session Hardening** — HttpOnly, SameSite=Lax cookies, ID regeneration
- **Password Hashing** — bcrypt cost 12
- **Role Middleware** — enforced per route group
- **Audit Logging** — every write action recorded with before/after values
- **Activity Logging** — user activity trail
- **Security Headers** — X-Frame-Options, X-XSS-Protection, X-Content-Type-Options

---

## Architecture

```
Request → public/index.php
        → Bootstrap (Config, Session, Logger)
        → Router::dispatch()
        → Middleware chain (Auth → CSRF → Role)
        → Controller::method()
        → Model (PDO queries)
        → View render (layout wrapping)
        → Response
```

---

## Adding a New Route

```php
// routes/web.php
$router->get('/my-page', ['MyController', 'index'], ['auth']);
$router->post('/my-page/save', ['MyController', 'store'], ['auth', 'csrf']);
```

## Adding a New Controller

```php
// app/Controllers/MyController.php
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Request;

class MyController extends Controller {
    public function index(Request $request): void {
        $this->render('my-page.index', ['title' => 'My Page']);
    }
}
```

## Adding a New Model

```php
// app/Models/MyModel.php
namespace App\Models;
use App\Core\Model;

class MyModel extends Model {
    protected string $table    = 'my_table';
    protected array  $fillable = ['col1', 'col2'];
}
```

---

## Database Schema

18 tables total:

| Table               | Purpose                          |
|---------------------|----------------------------------|
| users               | All users (all roles)            |
| venues              | Venue listings                   |
| venue_images        | Venue photo gallery              |
| subscription_plans  | Plan definitions                 |
| subscriptions       | User subscriptions               |
| payments            | Payment records                  |
| bookings            | Court bookings                   |
| reviews             | Venue reviews                    |
| notifications       | User notifications               |
| support_tickets     | Support system                   |
| audit_logs          | Admin action audit trail         |
| activity_logs       | User activity feed               |
| settings            | Key-value system settings        |

---

## Tech Stack

| Layer        | Technology              |
|--------------|-------------------------|
| Language     | PHP 8.0+                |
| Database     | MySQL 5.7+ / MariaDB    |
| Frontend     | Bootstrap 5.3 + Chart.js|
| Icons        | Bootstrap Icons 1.11    |
| Fonts        | Plus Jakarta Sans, Inter|
| Autoloading  | PSR-4 (Composer / manual)|
| Architecture | MVC + Service Layer     |
| Auth         | Session-based + RBAC    |

---

## License

Proprietary — Findownn © 2026. All rights reserved.
