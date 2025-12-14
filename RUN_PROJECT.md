# 🚀 How to Run the Project

## Quick Start (PHP Built-in Server)

### Step 1: Setup Database
1. Start **XAMPP/WAMP** Control Panel
2. Start **MySQL** service
3. Open **phpMyAdmin**: http://localhost/phpmyadmin
4. Create database: `student_result_system`
5. Import `database/schema.sql` file

### Step 2: Start PHP Server

**Option A: Using Batch File**
- Double-click `start_server.bat`

**Option B: Using Command Line**
```bash
cd "d:\github project\Student-Result-Management-System-1"
php -S localhost:8000
```

### Step 3: Access Application
- Open browser: **http://localhost:8000**
- Login with:
  - Admin: `admin` / `password`
  - Staff: `staff1` / `password`
  - Student: `student1` / `password`

---

## Using XAMPP/WAMP (Alternative)

### Step 1: Move Project
Copy project folder to:
- **XAMPP**: `C:\xampp\htdocs\Student-Result-Management-System-1\`
- **WAMP**: `C:\wamp64\www\Student-Result-Management-System-1\`

### Step 2: Setup Database
- Same as Quick Start Step 1 above

### Step 3: Update BASE_URL
Edit `config/config.php`:
```php
define('BASE_URL', 'http://localhost/Student-Result-Management-System-1/');
```

### Step 4: Start Services
- Start **Apache** and **MySQL** in XAMPP/WAMP Control Panel

### Step 5: Access Application
- Open browser: **http://localhost/Student-Result-Management-System-1/**

---

## 📝 Login Credentials

### Admin Account
- Username: `admin`
- Password: `password`
- Access: Full system access, view all queries, statistics

### Staff Account
- Username: `staff1`, `staff2`, or `staff3`
- Password: `password`
- Access: Post marks, view posted marks, view queries

### Student Account
- Username: `student1`, `student2`, `student3`, `student4`, or `student5`
- Password: `password`
- Access: View own results, submit queries

---

## ✅ Verification Checklist

After starting the server, verify:

- [ ] Login page loads at http://localhost:8000
- [ ] Can login as admin
- [ ] Admin dashboard shows statistics
- [ ] Can login as staff
- [ ] Staff dashboard accessible
- [ ] Can login as student
- [ ] Student can view results

---

## 🔧 Troubleshooting

### "Database connection failed"
- ✅ Check MySQL is running
- ✅ Verify database `student_result_system` exists
- ✅ Check credentials in `config/database.php`

### "Page not found"
- ✅ Verify server is running
- ✅ Check URL matches BASE_URL in config
- ✅ Try: http://localhost:8000/index.php

### "Cannot login"
- ✅ Verify database has sample data
- ✅ Check password hash format (bcrypt)
- ✅ Clear browser cache

### Port 8000 already in use
Change port in `start_server.bat` or command:
```bash
php -S localhost:8080
```

---

**Server is now running!** 🎉

Access the application in your browser.

