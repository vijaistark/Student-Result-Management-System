# Database Setup Guide

## 🔴 Error: Access Denied for User 'root'@'localhost'

This error means MySQL requires a password for the root user, or the connection method needs adjustment.

## ✅ Solution Steps

### Step 1: Determine Your MySQL Password

#### Option A: XAMPP Default (No Password)
- XAMPP MySQL usually has **no password** by default
- Use: `define('DB_PASS', '');` (empty string)

#### Option B: WAMP Default (No Password)
- WAMP MySQL usually has **no password** by default  
- Use: `define('DB_PASS', '');` (empty string)

#### Option C: MAMP Default (Password: root)
- MAMP MySQL usually uses password: `root`
- Use: `define('DB_PASS', 'root');`

#### Option D: Custom Password
- If you set a custom password, use that
- Use: `define('DB_PASS', 'your_password');`

### Step 2: Update Database Configuration

Edit `config/database.php` and update the password:

```php
// Try one of these:
define('DB_PASS', '');        // For XAMPP/WAMP (no password)
define('DB_PASS', 'root');    // For MAMP or if you set password to 'root'
define('DB_PASS', 'your_password');  // Your custom password
```

### Step 3: Test the Connection

1. Start MySQL service (XAMPP/WAMP Control Panel)
2. Refresh your browser: http://localhost:8000
3. If still error, try next solution

## 🔧 Alternative Solutions

### Solution 1: Reset MySQL Root Password (XAMPP/WAMP)

If you forgot your password or want to remove it:

**For XAMPP:**
1. Stop MySQL in XAMPP Control Panel
2. Open Command Prompt as Administrator
3. Navigate to: `C:\xampp\mysql\bin`
4. Run:
   ```bash
   mysqld --skip-grant-tables
   ```
5. In another terminal:
   ```bash
   mysql -u root
   ```
6. In MySQL prompt:
   ```sql
   ALTER USER 'root'@'localhost' IDENTIFIED BY '';
   FLUSH PRIVILEGES;
   EXIT;
   ```
7. Restart MySQL from XAMPP Control Panel

**For WAMP:**
Similar process, but navigate to: `C:\wamp64\bin\mysql\mysql[version]\bin`

### Solution 2: Use phpMyAdmin to Check

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. If it opens without password → Use `''` (empty) in config
3. If it asks for password → Use that password in config

### Solution 3: Create a New MySQL User

If you can't access root, create a new user:

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "User accounts" tab
3. Click "Add user account"
4. Username: `student_db_user`
5. Password: `student_db_pass` (or your choice)
6. Host: `localhost`
7. Check "Grant all privileges on database `student_result_system`"
8. Click "Go"

Then update `config/database.php`:
```php
define('DB_USER', 'student_db_user');
define('DB_PASS', 'student_db_pass');
```

## 📝 Quick Test

After updating `config/database.php`, test connection:

```php
<?php
// test_connection.php (temporary file)
require_once 'config/database.php';
$conn = getDBConnection();
if ($conn) {
    echo "✅ Database connection successful!";
    $conn->close();
} else {
    echo "❌ Database connection failed!";
}
?>
```

Access: http://localhost:8000/test_connection.php

**Delete this file after testing for security!**

## 🎯 Common Password Configurations

| Setup | Username | Password | DB_PASS Value |
|-------|----------|----------|---------------|
| XAMPP (default) | root | (none) | `''` |
| WAMP (default) | root | (none) | `''` |
| MAMP (default) | root | root | `'root'` |
| Custom | root | your_pass | `'your_pass'` |

## ✅ Verification

Once configured correctly:
1. Database connection should work
2. Login page should load
3. You can login with: `admin` / `password`

---

**Still having issues?** Check:
- MySQL service is running
- Database `student_result_system` exists
- Credentials in `config/database.php` are correct
- Try accessing phpMyAdmin to verify password

