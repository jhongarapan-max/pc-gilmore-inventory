# PC Gilmore Inventory System - Quick Reference

## 🚀 Quick Start

### Installation (5 minutes)
1. Install XAMPP
2. Start Apache & MySQL
3. Import `database/schema.sql` in phpMyAdmin
4. Access: `http://localhost/pc-gilmore-inventory-system/`
5. Login: `admin` / `admin123`

## 🔑 Default Credentials
- **Username:** admin
- **Password:** admin123
- ⚠️ Change password immediately!

## 📱 Main Modules

### Dashboard
- Quick stats overview
- Recent sales
- Low stock alerts
- Activity feed

### Inventory
- **Add Item:** Scan/enter barcode, fill details, save
- **Edit Item:** Update prices, stock levels, details
- **Search:** Filter by name, barcode, category, stock level
- **Export:** CSV export for Excel

### Scanner
- Focus on input → Scan barcode → Item displays
- Works with any USB HID barcode scanner
- Manual search option available
- Auto-clears for next scan

### POS (Point of Sale)
1. Scan items to add to cart
2. Adjust quantities if needed
3. Click "Checkout"
4. Enter payment details
5. Complete sale → Print receipt

### Stock Management
**Stock In:**
- Select item → Enter quantity → Add cost → Save
- Auto-updates inventory
- Creates audit log

**Stock Out:**
- Select item → Enter quantity → State reason → Save
- Requires justification for audit

### Suppliers
- Add/edit supplier contacts
- Track supplier information
- Link to purchase orders

### Reports
- Sales summary by date range
- Top selling items
- Low stock alerts
- Payment method breakdown
- Daily sales trends
- Print/export capability

### Users (Admin Only)
- Add new users
- Assign roles
- Manage permissions
- View last login
- Deactivate users

## 👥 User Roles

| Role | Access |
|------|--------|
| **Admin** | Everything including user management |
| **Manager** | Inventory, POS, Stock, Reports, Suppliers |
| **Cashier** | POS and Scanner only |
| **Warehouse** | Inventory, Stock, Scanner |
| **Viewer** | Reports and Scanner (read-only) |

## 🔍 Test Barcodes
```
8886419353430 - Intel Core i5-12400F
4711081334347 - ASUS TUF Gaming Laptop
0840006642619 - Logitech MX Master 3 Mouse
8436589771404 - Kingston Fury 16GB DDR4 RAM
0740617261028 - Samsung 970 EVO Plus 500GB
```

## ⌨️ Keyboard Shortcuts
- **POS:** Tab between fields, Enter to scan
- **Scanner:** Auto-focus on input field
- **Forms:** Tab navigation, Enter to submit

## 🔧 Common Tasks

### Add New Item
1. Inventory → Add New Item
2. Scan/enter barcode
3. Fill name, category, prices
4. Set initial stock & min level
5. Save

### Make a Sale
1. POS → Focus on barcode field
2. Scan items (adds to cart)
3. Adjust quantities if needed
4. Checkout → Enter payment
5. Print receipt

### Receive Stock Delivery
1. Stock → Stock In
2. Scan/select item
3. Enter quantity received
4. Enter unit cost
5. Add PO reference number
6. Save (updates inventory)

### Check Low Stock
- Dashboard → View Low Stock panel
- Inventory → Filter by "Low Stock"
- Reports → Low Stock section

### View Sales Report
1. Reports module
2. Select date range
3. View statistics
4. Print or export

### Add New User
1. Users → Add New User (Admin only)
2. Enter username, name, email
3. Set password (min 6 chars)
4. Select role
5. Save

## 📊 Reports Available

1. **Sales Summary**
   - Total sales amount
   - Transaction count
   - Average transaction value

2. **Top Sellers**
   - Top 10 items by quantity
   - Revenue per item

3. **Low Stock**
   - Items below minimum level
   - Current vs minimum stock

4. **Payment Methods**
   - Breakdown by payment type
   - Transaction counts

5. **Daily Sales**
   - Sales per day
   - Transaction trends

## 🛡️ Security Features

✅ Password hashing (bcrypt)
✅ SQL injection protection (PDO)
✅ XSS prevention
✅ Session timeout (8 hours)
✅ Role-based access
✅ Audit logging
✅ CSRF protection ready

## 💾 Backup

### Database Backup
1. phpMyAdmin
2. Select `pc_gilmore_inventory`
3. Export → Quick → Go
4. Save SQL file

### Restore
1. phpMyAdmin
2. Select database
3. Import → Choose file → Go

## 🐛 Troubleshooting

**Can't login:**
- Check database is imported
- Verify MySQL is running
- Clear browser cache

**Scanner not working:**
- Test in Notepad first
- Ensure keyboard mode
- Click input field

**Low stock not showing:**
- Set min_stock_level for items
- Ensure stock is below minimum
- Refresh page

**Database error:**
- Check MySQL service
- Verify credentials in config/database.php
- Check database exists

**Permission denied:**
- Verify user role
- Check session is active
- Re-login if needed

## 📞 Support Files

- **README.md** - Full documentation
- **INSTALL.md** - Setup guide
- **SYSTEM_INFO.md** - Technical details
- This file - Quick reference

## 🎯 Daily Checklist

### Morning
- [ ] Check low stock alerts
- [ ] Review yesterday's sales
- [ ] Verify system access

### During Day
- [ ] Process stock deliveries
- [ ] Handle POS transactions
- [ ] Update inventory as needed

### End of Day
- [ ] Run daily sales report
- [ ] Check stock levels
- [ ] Backup database

## 📈 Best Practices

1. **Always scan barcodes** when possible (faster, accurate)
2. **Set min stock levels** for automatic alerts
3. **Use reference numbers** for stock movements
4. **Regular backups** (daily recommended)
5. **Strong passwords** for all users
6. **Deactivate** users who leave (don't delete)
7. **Review reports** weekly
8. **Check audit logs** for irregularities

## 🎓 Training Tips

**For Cashiers:**
- Practice scanning
- Know payment methods
- Always print receipt

**For Warehouse:**
- Update stock immediately
- Use reference numbers
- Check stock levels daily

**For Managers:**
- Review reports weekly
- Monitor low stock
- Check audit logs
- Manage suppliers

**For Admin:**
- User management
- System security
- Database backups
- Full system oversight

## 🔗 Quick Links

- **System:** http://localhost/pc-gilmore-inventory-system/
- **phpMyAdmin:** http://localhost/phpmyadmin
- **XAMPP Control:** C:\xampp\xampp-control.exe

## 📝 Version Info

- **Version:** 1.0.0
- **Status:** Production Ready ✅
- **Last Updated:** February 2026
- **PHP Version Required:** 7.4+
- **MySQL Version Required:** 5.7+

---

**Need detailed help?** See README.md  
**Installation help?** See INSTALL.md  
**Technical details?** See SYSTEM_INFO.md

**System is ready! Start selling! 🛒**
