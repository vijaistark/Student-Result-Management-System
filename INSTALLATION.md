# Quick Installation Guide

## Prerequisites Checklist

- [ ] XAMPP/WAMP/LAMP installed
- [ ] Apache and MySQL services running
- [ ] PHP 7.4 or higher
- [ ] phpMyAdmin accessible

## Step-by-Step Installation

### 1. Extract Project
Extract the project folder to:
- **XAMPP**: `C:\xampp\htdocs\Student-Result-Management-System-1\`
- **WAMP**: `C:\wamp64\www\Student-Result-Management-System-1\`
- **Linux**: `/var/www/html/Student-Result-Management-System-1/`

### 2. Create Database

**Option A: Using phpMyAdmin**
1. Open `http://localhost/phpmyadmin`
2. Click "New" to create database
3. Name: `student_result_system`
4. Collation: `utf8mb4_general_ci`
5. Click "Create"

**Option B: Using Command Line**
```bash
mysql -u root -p
CREATE DATABASE student_result_system CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
exit;
```

### 3. Import Schema

**Option A: Using phpMyAdmin**
1. Select `student_result_system` database
2. Click "Import" tab
3. Choose file: `database/schema.sql`
4. Click "Go"

**Option B: Using Command Line**
```bash
mysql -u root -p student_result_system < database/schema.sql
```

### 4. Configure Database

Edit `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Your MySQL username
define('DB_PASS', '');            // Your MySQL password (leave empty if none)
define('DB_NAME', 'student_result_system');
```

### 5. Configure Base URL

Edit `config/config.php` and update:

```php
// For standard XAMPP setup:
define('BASE_URL', 'http://localhost/Student-Result-Management-System-1/');

// If using different port:
// define('BASE_URL', 'http://localhost:8080/Student-Result-Management-System-1/');
```

### 6. Set Permissions (Linux/Mac)

```bash
chmod -R 755 Student-Result-Management-System-1
chmod -R 777 Student-Result-Management-System-1/assets
```

### 7. Test Installation

1. Open browser: `http://localhost/Student-Result-Management-System-1/`
2. Login with:
   - **Admin**: `admin` / `password`
   - **Staff**: `staff1` / `password`
   - **Student**: `student1` / `password`

## Verification Checklist

- [ ] Login page loads without errors
- [ ] Can login as admin
- [ ] Admin dashboard shows statistics
- [ ] Can login as staff
- [ ] Staff can post marks
- [ ] Can login as student
- [ ] Student can view their results
- [ ] Student can submit queries

## Common Issues

### "Database connection failed"
- Check MySQL is running
- Verify credentials in `config/database.php`
- Ensure database exists

### "Page not found" or blank page
- Check BASE_URL in `config/config.php`
- Verify Apache is running
- Check file permissions
- Look at PHP error logs

### "Password incorrect" even with correct password
- Clear browser cache and cookies
- Verify password hash in database matches
- Try regenerating password hash using `utils/generate_password_hash.php`

### Marks not saving
- Check database UNIQUE constraint
- Verify foreign key relationships
- Check PHP error logs
- Ensure staff has permission for that subject

## Post-Installation

1. **Change Default Passwords**: Generate new password hashes using `utils/generate_password_hash.php`
2. **Update Sample Data**: Replace sample students/staff with real data
3. **Configure Email** (if needed): For query notifications
4. **Set Up Backups**: Regular database backups recommended

## Support

If you encounter issues:
1. Check PHP error logs: `C:\xampp\php\logs\php_error_log`
2. Check Apache error logs: `C:\xampp\apache\logs\error.log`
3. Enable PHP error display (temporarily) in `php.ini`:
   ```ini
   display_errors = On
   error_reporting = E_ALL
   ```

---

**Installation Complete!** 🎉

