# Quick Start Guide

## 🚀 Running the Project

### Method 1: Using XAMPP/WAMP (Recommended)

1. **Start Services:**
   - Open XAMPP/WAMP Control Panel
   - Start **Apache** and **MySQL** services

2. **Setup Database:**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Click "New" → Create database: `student_result_system`
   - Select the database → Click "Import" tab
   - Choose file: `database/schema.sql`
   - Click "Go"

3. **Configure Database Connection:**
   - Edit `config/database.php`
   - Update if needed (usually works with default XAMPP settings):
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');  // Leave empty for XAMPP default
     ```

4. **Access Application:**
   - Open browser: `http://localhost/Student-Result-Management-System-1/`
   - Or if in htdocs: `http://localhost/Student-Result-Management-System-1/index.php`

### Method 2: Using PHP Built-in Server

1. **Setup Database** (using phpMyAdmin as in Method 1)

2. **Start PHP Server:**
   ```bash
   # In project directory
   php -S localhost:8000
   ```
   Or double-click `start_server.bat`

3. **Access Application:**
   - Open browser: `http://localhost:8000`

## 📝 Default Login Credentials

### Admin
- **Username:** `admin`
- **Password:** `password`

### Staff
- **Username:** `staff1` (or `staff2`, `staff3`)
- **Password:** `password`

### Student
- **Username:** `student1` (or `student2`, `student3`, `student4`, `student5`)
- **Password:** `password`

## 🔧 Troubleshooting

### Database Connection Error
- Ensure MySQL is running (check XAMPP/WAMP control panel)
- Verify credentials in `config/database.php`
- Check database exists: Open phpMyAdmin and verify `student_result_system` database

### Page Not Found (404)
- Verify Apache is running
- Check file path in browser matches your folder location
- Try: `http://localhost/Student-Result-Management-System-1/index.php`

### Blank Page
- Check PHP error logs
- Enable error display temporarily in `php.ini`:
  ```ini
  display_errors = On
  error_reporting = E_ALL
  ```

### Cannot Login
- Clear browser cache and cookies
- Verify database has sample data (check `users` table in phpMyAdmin)
- Check password hash in database matches bcrypt format

## 📊 Verify Installation

1. ✅ Login page loads without errors
2. ✅ Can login as admin
3. ✅ Admin dashboard shows statistics
4. ✅ Can login as staff
5. ✅ Staff can view/post marks
6. ✅ Can login as student
7. ✅ Student can view results

## 🎯 Next Steps

1. **Change Default Passwords** (Security)
2. **Add Your Own Data** (Students, Subjects)
3. **Customize** (Styles, Branding)
4. **Deploy** (Production Server)

---

**Need Help?** Check `README.md` and `INSTALLATION.md` for detailed documentation.

