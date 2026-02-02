# PC Gilmore Inventory & POS System

A complete, production-ready web-based inventory management and point-of-sale (POS) system designed for PC Gilmore - a single-branch computer retailer and wholesaler.

## 🎯 Features

### Core Functionality
- **Inventory Management** - Complete CRUD operations for items with barcode/QR support
- **Point of Sale (POS)** - Fast checkout with barcode scanning and receipt printing
- **Barcode Scanner** - Real-time item lookup using USB barcode scanners
- **Stock Management** - Stock In/Out tracking with full audit trail
- **Supplier Management** - Track suppliers and purchase orders
- **Reports & Analytics** - Sales reports, low stock alerts, top selling items
- **User Management** - Role-based access control (RBAC)
- **Audit Logging** - Complete trail of all system actions

### Security Features
- Password hashing (bcrypt)
- PDO prepared statements (SQL injection protection)
- Session management with timeout
- CSRF token protection
- Role-based access control
- Input sanitization

### User Roles
1. **Admin** - Full system access including user management
2. **Manager** - Inventory, POS, Stock, Reports, Suppliers
3. **Cashier** - POS and Scanner only
4. **Warehouse** - Inventory, Stock movements, Scanner
5. **Viewer** - Reports and Scanner (read-only)

## 🛠️ Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend:** PHP 7.4+ with PDO
- **Database:** MySQL 5.7+ / MariaDB 10.3+
- **Server:** Apache/Nginx (XAMPP recommended for Windows)

## 📋 System Requirements

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- 256MB RAM minimum (512MB recommended)
- USB Barcode Scanner (optional, works as keyboard input)

## 🚀 Installation Guide

### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP to `C:\xampp\`
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Setup Project Files
1. Copy the entire `pc-gilmore-inventory-system` folder to `C:\xampp\htdocs\`
2. Final path should be: `C:\xampp\htdocs\pc-gilmore-inventory-system\`

### Step 3: Create Database
1. Open phpMyAdmin at `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Choose file: `database/schema.sql`
4. Click "Go" to import

**OR** manually:
1. Create a new database named `pc_gilmore_inventory`
2. Import the `database/schema.sql` file

### Step 4: Configure Database Connection
1. Open `config/database.php`
2. Update credentials if needed (default works with XAMPP):
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'pc_gilmore_inventory');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Step 5: Access the System
1. Open browser and go to: `http://localhost/pc-gilmore-inventory-system/`
2. Login with default credentials:
   - **Username:** `admin`
   - **Password:** `admin123`
3. **IMPORTANT:** Change the admin password immediately after first login!

## 📁 Project Structure

```
pc-gilmore-inventory-system/
├── assets/
│   ├── css/
│   │   └── style.css           # Main stylesheet
│   └── js/
│       └── main.js             # JavaScript functions
├── config/
│   ├── config.php              # App configuration
│   └── database.php            # Database connection
├── database/
│   └── schema.sql              # Database schema
├── includes/
│   ├── functions.php           # Helper functions
│   ├── session.php             # Session management
│   ├── header.php              # Page header/navbar
│   └── footer.php              # Page footer
├── modules/
│   ├── inventory/              # Inventory management
│   │   ├── index.php
│   │   ├── add.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── scanner/                # Barcode scanner
│   │   ├── index.php
│   │   └── scan_api.php
│   ├── pos/                    # Point of Sale
│   │   ├── index.php
│   │   ├── pos_api.php
│   │   └── receipt.php
│   ├── stock/                  # Stock management
│   │   ├── stock_in.php
│   │   ├── stock_out.php
│   │   └── history.php
│   ├── suppliers/              # Supplier management
│   │   ├── index.php
│   │   ├── add.php
│   │   ├── edit.php
│   │   └── delete.php
│   ├── reports/                # Reports & analytics
│   │   └── index.php
│   └── users/                  # User management
│       ├── index.php
│       ├── add.php
│       ├── edit.php
│       └── delete.php
├── init.php                    # Bootstrap file
├── index.php                   # Dashboard
├── login.php                   # Login page
├── logout.php                  # Logout handler
├── profile.php                 # User profile
├── unauthorized.php            # Access denied page
└── README.md                   # This file
```

## 💻 Usage Guide

### Adding Items to Inventory
1. Navigate to **Inventory** → **Add New Item**
2. Use barcode scanner or manually enter barcode
3. Fill in item details (name, category, prices, stock)
4. Set minimum stock level for low stock alerts
5. Click "Save Item"

### Using the Barcode Scanner
1. Navigate to **Scanner** page
2. Click on the input field (auto-focused)
3. Scan item barcode using USB scanner
4. Item details appear instantly
5. Input clears automatically for next scan

### Making a Sale (POS)
1. Navigate to **POS** module
2. Scan items to add to cart (or search manually)
3. Adjust quantities if needed
4. Click "Checkout"
5. Enter payment details
6. Complete sale and print receipt

### Managing Stock
**Stock In:**
1. Go to **Stock** → **Stock In**
2. Scan or select item
3. Enter quantity and unit cost
4. Add reference number (PO number, etc.)
5. Save

**Stock Out:**
1. Go to **Stock** → **Stock Out**
2. Scan or select item
3. Enter quantity to remove
4. Provide reason (damaged, transferred, etc.)
5. Save

### Viewing Reports
1. Navigate to **Reports**
2. Select date range
3. View:
   - Total sales and transactions
   - Top selling items
   - Low stock alerts
   - Payment method breakdown
   - Daily sales trends
4. Print or export reports

### Managing Users (Admin Only)
1. Navigate to **Users**
2. Click "Add New User"
3. Enter user details and select role
4. Assign appropriate permissions
5. Save user

## 🔐 Security Best Practices

1. **Change Default Password** - First thing after installation!
2. **Use Strong Passwords** - Minimum 6 characters (longer is better)
3. **Regular Backups** - Backup database regularly
4. **Update System** - Keep PHP and MySQL updated
5. **HTTPS** - Use SSL certificate in production
6. **Disable Errors** - Set `display_errors = 0` in production
7. **File Permissions** - Restrict write permissions to necessary files only

## 🔧 Configuration

### Session Timeout
Edit in `config/config.php`:
```php
define('SESSION_TIMEOUT', 28800); // 8 hours in seconds
```

### Timezone
Edit in `config/config.php`:
```php
date_default_timezone_set('Asia/Manila');
```

### Base URL
Edit in `config/config.php` if not using default:
```php
define('BASE_URL', 'http://localhost/pc-gilmore-inventory-system/');
```

## 🐛 Troubleshooting

### Can't Login
- Verify database is imported correctly
- Check database credentials in `config/database.php`
- Ensure MySQL service is running

### Barcode Scanner Not Working
- Scanner should work as keyboard input (HID device)
- Test scanner in notepad first
- Ensure cursor is focused on scan input field

### Database Connection Error
- Verify MySQL is running in XAMPP
- Check database name and credentials
- Ensure database exists and schema is imported

### Permission Errors
- Check user role assignments
- Verify session is active
- Clear browser cache and cookies

### Low Stock Alerts Not Showing
- Check min_stock_level is set for items
- Verify current stock is below minimum
- Refresh inventory page

## 🔄 Backup & Restore

### Backup Database
1. Open phpMyAdmin
2. Select `pc_gilmore_inventory` database
3. Click "Export"
4. Choose "Quick" method
5. Click "Go" to download

### Restore Database
1. Open phpMyAdmin
2. Select `pc_gilmore_inventory` database
3. Click "Import"
4. Choose backup file
5. Click "Go"

## 📊 Sample Data

The system includes sample data:
- 1 Admin user (admin/admin123)
- 8 Product categories
- 3 Suppliers
- 5 Sample items with barcodes

## 🎓 Training Notes

### For Cashiers
- Use POS module for all sales
- Scan items quickly using barcode scanner
- Always verify quantities before checkout
- Print receipt for customer

### For Warehouse Staff
- Update stock immediately upon receiving/shipping
- Use Stock In for deliveries
- Use Stock Out for damages/transfers
- Keep reference numbers for audit

### For Managers
- Monitor low stock alerts daily
- Review sales reports weekly
- Check audit logs regularly
- Manage supplier relationships

## 📝 Database Schema Overview

### Main Tables
- **users** - System users and authentication
- **items** - Product inventory
- **categories** - Product categories
- **suppliers** - Supplier information
- **sales** - Sales transactions
- **sale_items** - Individual items per sale
- **stock_movements** - Stock in/out history
- **purchase_orders** - Purchase orders
- **audit_logs** - System activity logs

## 🌐 Browser Compatibility

- Chrome 90+ ✅
- Firefox 88+ ✅
- Edge 90+ ✅
- Safari 14+ ✅
- Internet Explorer ❌ (Not supported)

## 📱 Mobile Responsive

The system is fully responsive and works on:
- Desktop computers
- Tablets
- Smartphones
- Touch-screen devices

## ⚙️ System Limits

- **Items:** Unlimited
- **Users:** Up to 50 recommended
- **Concurrent Users:** 6-8 optimal
- **Sales Records:** Database can handle millions
- **Barcode Length:** Up to 100 characters

## 🆘 Support

For issues or questions:
1. Check this README
2. Review troubleshooting section
3. Check error logs in `php_error.log`
4. Verify database integrity

## 📄 License

This system is developed for PC Gilmore. All rights reserved.

## 🎉 Credits

**System Name:** PC Gilmore Inventory & POS System  
**Version:** 1.0.0  
**Developed for:** PC Gilmore Computer Retailer & Wholesaler  
**Technology:** PHP, MySQL, Bootstrap 5  

---

**Last Updated:** February 2026  
**Status:** Production Ready ✅

## Quick Start Checklist

- [ ] Install XAMPP
- [ ] Copy files to htdocs
- [ ] Create database
- [ ] Import schema.sql
- [ ] Access http://localhost/pc-gilmore-inventory-system/
- [ ] Login with admin/admin123
- [ ] Change admin password
- [ ] Add users
- [ ] Configure categories
- [ ] Add suppliers
- [ ] Add inventory items
- [ ] Test barcode scanner
- [ ] Test POS checkout
- [ ] Setup daily backup schedule

**System is ready to use! Happy selling! 🛒**
