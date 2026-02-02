# PC Gilmore Inventory System - Installation Guide

## Quick Setup (5 Minutes)

### 1. Install XAMPP
Download and install XAMPP from: https://www.apachefriends.org/

### 2. Start Services
Open XAMPP Control Panel and start:
- ✅ Apache
- ✅ MySQL

### 3. Setup Database
1. Go to: http://localhost/phpmyadmin
2. Click "New" to create database
3. Database name: `pc_gilmore_inventory`
4. Click "Import" tab
5. Choose file: `database/schema.sql`
6. Click "Go"

### 4. Access System
1. Open browser
2. Go to: http://localhost/pc-gilmore-inventory-system/
3. Login:
   - Username: `admin`
   - Password: `admin123`

### 5. Change Password
Go to Profile → Change Password (Important!)

## Default Credentials

**Admin Account:**
- Username: admin
- Password: admin123

## Sample Data Included

The system comes with:
- ✅ 1 Admin user
- ✅ 8 Product categories
- ✅ 3 Sample suppliers
- ✅ 5 Sample items with barcodes

## Test Barcodes

Use these barcodes to test the scanner:
```
8886419353430 - Intel Core i5-12400F Processor
4711081334347 - ASUS TUF Gaming Laptop
0840006642619 - Logitech MX Master 3 Mouse
8436589771404 - Kingston Fury 16GB DDR4 RAM
0740617261028 - Samsung 970 EVO Plus 500GB
```

## File Structure

```
pc-gilmore-inventory-system/
├── config/          # Configuration files
├── includes/        # Common PHP includes
├── modules/         # Feature modules
├── assets/          # CSS, JS, images
├── database/        # SQL schema
├── init.php         # Bootstrap
└── index.php        # Dashboard
```

## Common Issues

**Database Connection Error:**
- Check MySQL is running in XAMPP
- Verify credentials in config/database.php

**Can't Login:**
- Make sure database is imported
- Clear browser cache

**Barcode Scanner Not Working:**
- Ensure scanner is in keyboard mode
- Test scanner in Notepad first

## Next Steps

1. ✅ Add more users
2. ✅ Configure categories
3. ✅ Add inventory items
4. ✅ Test POS system
5. ✅ Setup regular backups

## Need Help?

Check the full README.md for detailed documentation.

---
**System Version:** 1.0.0  
**Ready for Production** ✅
