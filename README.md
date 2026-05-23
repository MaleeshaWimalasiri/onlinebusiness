# Maheesha Jewels — Online Jewelry Store

A complete online business website for a fine-jewelry store, built as a
**Web Application Development** coursework assignment.

The site has a customer-facing storefront and a separate **admin panel**, and
uses a MySQL database with all the **DML operations** (INSERT, SELECT, UPDATE,
DELETE).

## Technology Stack

| Layer     | Technology                          |
|-----------|-------------------------------------|
| Frontend  | HTML5, CSS3, JavaScript, Bootstrap 5 |
| Backend   | PHP 8 (PDO, prepared statements)     |
| Database  | MySQL / MariaDB                      |

## Features

### Storefront (customer side)
- **Home** — hero banner, shop-by-category, new arrivals
- **Shop** — product listing with category filter and search
- **Product detail** — full description, customer reviews & ratings
- **Shopping cart** — add / update quantity / remove items
- **Checkout** — places an order (saved to the database)
- **Contact** — inquiry form saved to the database
- **About** — company information
- **User registration & login** — customer accounts
- **My Account** — profile and order history

### Admin panel (`/admin`)
- **Dashboard** — summary statistics, recent orders, low-stock alerts
- **Products** — add / edit / delete products (full CRUD), with photo upload
  (uploaded images are stored in `assets/images/uploads/`)
- **Categories** — add / edit / delete product categories (full CRUD)
- **Orders** — view orders, update status, delete
- **Messages** — read and delete contact messages
- **Users** — view users, change role, delete

### DML operations used
- **INSERT** — new products, orders, order items, reviews, messages, users
- **SELECT** — every listing, search, login, dashboard statistics
- **UPDATE** — edit products, order status, stock levels, message read flag, user role
- **DELETE** — remove products, orders, messages, users

## Setup Instructions

1. **Install XAMPP** and start **Apache** and **MySQL**.
2. **Copy the project** into the XAMPP web root:
   - Windows: `C:\xampp\htdocs\Online-Business`
   - macOS: `/Applications/XAMPP/htdocs/Online-Business`
3. **Import the database**:
   - Open phpMyAdmin: <http://localhost/phpmyadmin>
   - Click **Import**, choose `database/jewelry_store.sql`, then **Go**.
   - (This creates the `jewelry_store` database with sample data.)
4. **Check the database settings** in `includes/config.php`.
   The defaults match a standard XAMPP install (`root`, no password).
5. **Open the site** in your browser:
   <http://localhost/Online-Business/index.php>

## Demo Login Accounts

| Role     | Email                     | Password     |
|----------|---------------------------|--------------|
| Admin    | admin@maheeshajewels.com  | admin123     |
| Customer | sara@example.com          | password123  |

The admin panel is available at
<http://localhost/Online-Business/admin/index.php> (log in as the admin first).

## Project Structure

```
Online-Business/
├── index.php            Home page
├── products.php         Shop / product listing
├── product.php          Product detail + reviews
├── cart.php             Shopping cart
├── checkout.php         Place an order
├── contact.php          Contact / inquiry form
├── about.php            About page
├── register.php         Customer registration
├── login.php            Login (customer + admin)
├── logout.php           Logout
├── account.php          Customer account + order history
├── admin/               Admin panel
│   ├── index.php        Dashboard
│   ├── products.php     Manage products (CRUD)
│   ├── orders.php       Manage orders
│   ├── messages.php     Manage messages
│   ├── users.php        Manage users
│   └── includes/        Admin layout
├── includes/            Shared config, functions, header, footer
├── assets/              CSS, JavaScript, images
└── database/
    └── jewelry_store.sql   Database schema + sample data
```

## Security Notes
- All database access uses **PDO prepared statements** (prevents SQL injection).
- All output is escaped with `htmlspecialchars()` (prevents XSS).
- Passwords are stored using PHP `password_hash()` (bcrypt).
