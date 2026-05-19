# EffaFashion — Luxury Fashion E-Commerce

A full-featured PHP/MySQL fashion e-commerce platform with a dark elegant theme.

## 🚀 Setup Instructions

### Requirements
- XAMPP (Apache + MySQL + PHP 8.0+)
- Browser

### Installation

1. **Copy project** to `C:/xampp/htdocs/EffaFashion`

2. **Start XAMPP** — start Apache and MySQL

3. **Import database**
   - Open `http://localhost/phpmyadmin`
   - Create a new database named `effafashion`
   - Click **Import** → select `database/effafashion.sql` → click **Go**

4. **Configure** (if needed)
   - Open `config/db.php`
   - Update `DB_USER`, `DB_PASS`, `SITE_URL` if different from defaults

5. **Visit** `http://localhost/EffaFashion`

---

## 🔐 Default Admin Login
| Field    | Value                    |
|----------|--------------------------|
| Email    | admin@effafashion.com    |
| Password | password                 |

> **Note:** The seed data uses `password_hash('password', PASSWORD_DEFAULT)` — update this after first login.

---

## 📁 Project Structure

```
EffaFashion/
├── admin/                  # Admin panel
│   ├── includes/           # Admin header/footer
│   ├── dashboard.php       # Analytics dashboard
│   ├── add-product.php     # Add new product
│   ├── edit-product.php    # Manage products
│   ├── orders.php          # Order management
│   ├── users.php           # Customer management
│   └── reports.php         # Sales reports
├── ajax/                   # AJAX endpoints
│   ├── cart.php
│   ├── wishlist.php
│   ├── coupon.php
│   ├── newsletter.php
│   └── search.php
├── assets/
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript
│   └── images/             # Static images
├── config/
│   └── db.php              # Database config
├── database/
│   └── effafashion.sql     # Database schema + seed data
├── includes/               # Shared PHP includes
├── uploads/products/       # Uploaded product images
├── index.php               # Homepage
├── products.php            # Shop / product listing
├── product-details.php     # Single product page
├── cart.php                # Shopping cart
├── checkout.php            # Checkout
├── order-success.php       # Order confirmation
├── orders.php              # Order history
├── wishlist.php            # Wishlist
├── profile.php             # User profile
├── login.php               # Login
├── register.php            # Register
├── logout.php              # Logout
├── about.php               # About page
└── contact.php             # Contact page
```

---

## ✨ Features

| Feature | Status |
|---------|--------|
| Dark elegant theme (Black/Gold/White) | ✅ |
| Responsive design (mobile-first) | ✅ |
| Product search & filters | ✅ |
| Filter by category / price | ✅ |
| Product reviews & ratings | ✅ |
| Wishlist | ✅ |
| Shopping cart (AJAX) | ✅ |
| Coupon/discount codes | ✅ |
| Checkout & order placement | ✅ |
| Order tracking | ✅ |
| User profile & account | ✅ |
| Admin dashboard with charts | ✅ |
| Admin product management | ✅ |
| Admin order management | ✅ |
| Admin customer management | ✅ |
| Sales reports & analytics | ✅ |
| Newsletter subscription | ✅ |
| SweetAlert2 notifications | ✅ |
| Font Awesome icons | ✅ |

---

## 🎨 Design Colors
- **Black:** `#000000`
- **Gold:** `#D4AF37`
- **White:** `#FFFFFF`
- **Gray Background:** `#F5F5F5`

## 🛠 Technologies
- **Frontend:** HTML5, CSS3 (Flexbox + Grid), JavaScript (ES6+)
- **Backend:** PHP 8+
- **Database:** MySQL
- **Icons:** Font Awesome 6
- **Alerts:** SweetAlert2
- **Charts:** Chart.js
- **Server:** XAMPP
