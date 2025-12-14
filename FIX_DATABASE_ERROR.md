# 🔧 Fix: Database Connection Error

## Error Message
```
Access denied for user 'root'@'localhost' (using password: NO)
```

## ✅ Quick Fix

### Step 1: Test Database Connection

1. **Open this file in your browser:**
   ```
   http://localhost:8000/test_db_connection.php
   ```

2. **The script will try different passwords and tell you which one works**

3. **Note the password that shows ✅ SUCCESS**

### Step 2: Update Configuration

1. **Open:** `config/database.php`

2. **Find this line:**
   ```php
   define('DB_PASS', '');
   ```

3. **Update with the password that worked:**
   ```php
   define('DB_PASS', 'root');  // or '', or 'your_password'
   ```

### Step 3: Refresh Your Application

- Go back to: http://localhost:8000
- The error should be gone!

### Step 4: Setup Database (If Not Done)

If the test shows database doesn't exist:

1. **Open phpMyAdmin:** http://localhost/phpmyadmin
2. **Create database:**
   - Click "New"
   - Database name: `student_result_system`
   - Click "Create"
3. **Import schema:**
   - Select `student_result_system` database
   - Click "Import" tab
   - Choose file: `database/schema.sql`
   - Click "Go"

### Step 5: Delete Test File (Security)

**IMPORTANT:** Delete `test_db_connection.php` after use for security!

---

## 🔍 Manual Password Detection

If the test script doesn't work, try these common passwords:

### Common MySQL Passwords:
1. **No password (empty):** `''`
2. **root:** `'root'`
3. **password:** `'password'`
4. **admin:** `'admin'`

### Update config/database.php:
```php
define('DB_PASS', '');        // Try this first
// OR
define('DB_PASS', 'root');    // Try this second
```

---

## 📋 Still Not Working?

1. **Check MySQL is running:**
   - XAMPP/WAMP Control Panel → MySQL should be green/running

2. **Try phpMyAdmin:**
   - Open: http://localhost/phpmyadmin
   - If it works, note what password/login you used

3. **Reset MySQL password:**
   - See `DATABASE_SETUP.md` for detailed instructions

4. **Check database exists:**
   - In phpMyAdmin, verify `student_result_system` database exists

---

**After fixing, your application should work!** 🎉

