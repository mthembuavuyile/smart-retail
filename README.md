# Smart Retail System

A full-stack e-commerce web application built with PHP, MySQL, and vanilla JavaScript. Designed as a working demonstration of a modular retail platform with customer-facing storefront and internal admin panel.

## What It Does

**Customer side** — browse products, view details, manage a shopping cart, register/login, place orders, and review order history.

**Admin side** — dashboard with revenue stats and stock alerts, order management with status updates, inventory control, and sales/low-stock reports.

## Tech Stack

| Layer      | Technology                                   |
|------------|----------------------------------------------|
| Backend    | PHP 8.x, PDO (MySQL)                        |
| Database   | MySQL 5.7+ / MariaDB 10.3+                  |
| Frontend   | HTML5, CSS3 (vanilla), JavaScript (vanilla)  |
| Server     | Apache via AMPPS, XAMPP, or similar          |
| Typography | Inter (Google Fonts, loaded via CDN)         |

No frameworks, no npm, no build step. The application runs directly on any Apache/PHP/MySQL stack.

## Architecture

The project follows a lightweight MVC-style separation:

```
smart_retail_system/
├── config/              # Database credentials
├── database/            # SQL initialisation script
├── public/              # Web root — all URLs point here
│   ├── admin/           # Admin panel pages
│   ├── css/             # Stylesheets
│   ├── js/              # Client-side scripts
│   └── images/          # Product images (if local)
├── src/                 # Business logic — not publicly accessible
│   ├── Core/            # DB connection, helpers, session management
│   ├── Customer/        # Registration and authentication
│   ├── Product/         # Catalogue queries
│   ├── Order/           # Order creation and retrieval
│   ├── Inventory/       # Stock validation and updates
│   ├── Payment/         # Payment processing (simulated)
│   └── Reporting/       # Sales and inventory reports
└── templates/           # Shared header/footer partials
```

**`src/` is the heart of the backend.** Each module has a single Manager class that encapsulates all database interactions for that domain. Pages in `public/` instantiate these managers and call their methods — they never run raw SQL of their own.

**`templates/`** contains the reusable HTML chrome (nav, footer). Every page includes `header.php` at the top and `footer.php` at the bottom.

## Local Setup

### Prerequisites

- PHP 8.0 or newer
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` (any of AMPPS, XAMPP, MAMP, or Laragon will work)

### Steps

1. **Clone the repository** into your web server's document root:
   ```
   cd /path/to/your/www
   git clone https://github.com/your-username/smart_retail_system.git
   ```

2. **Create the database and seed data.** Open a MySQL client and run:
   ```
   mysql -u root -p < smart_retail_system/database/schema.sql
   ```
   This creates the `smart_retail_system` database, all tables, and inserts sample products, customers, and orders.

3. **Configure database credentials.** Edit `config/database.php` and set `DB_HOST`, `DB_USER`, `DB_PASS`, and `DB_NAME` to match your local MySQL setup. The defaults assume `root` on `localhost`.

4. **Open in your browser.** Navigate to:
   ```
   http://localhost/smart_retail_system/public/
   ```

### Demo Accounts

| Role     | Username / Email         | Password      |
|----------|--------------------------|---------------|
| Customer | avuyile@example.com      | password123   |
| Customer | sipho.n@example.com      | password123   |
| Admin    | admin                    | admin123      |

## Features

### Storefront
- Product catalogue with category labels and image cards
- Individual product pages with description, SKU, and quantity selector
- Session-based shopping cart (add, remove, view totals)
- Checkout with order summary and simulated payment
- Order confirmation with line-item breakdown
- Order history with status badges
- Customer registration and login with secure password hashing

### Admin Panel
- Dashboard with aggregate stats (revenue, orders, customers, products)
- Recent orders feed and low-stock alerts
- Full order list with inline status updates
- Inventory table with stock level editing and low-stock highlighting
- Sales report (last 7 days) and low-stock report

### Security Measures
- **Password hashing** — `password_hash()` with `PASSWORD_DEFAULT` (bcrypt)
- **Prepared statements** — every query uses PDO named parameters
- **XSS protection** — all user-generated output passes through `htmlspecialchars()`
- **Session hardening** — `HttpOnly`, `SameSite=Lax`, HTTPS-only when available, regenerated on login
- **Input sanitisation** — dedicated `sanitize_input()` and `sanitize_output()` helpers
- **CSRF mitigation** — forms use `POST` with `SameSite` cookies; session is regenerated on authentication

## Currency

All prices are in South African Rand (ZAR), displayed as `R 1 500.00`.

## License

This project is provided for educational and portfolio purposes.

---

Built by [Avuyile Mthembu](https://avuyilemthembu.co.za)
