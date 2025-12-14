# Smart Student Result Management System

A complete, role-based Student Result Management System built with MySQL, PHP, HTML, and CSS. The system provides separate dashboards for Admin, Staff, and Students with comprehensive features for managing academic results and queries.

## Features

### 🔐 Role-Based Authentication
- **Admin**: Full system access with statistics and query management
- **Staff**: Post marks (once per subject/student), view queries
- **Student**: View own results, raise queries/complaints

### 📊 Admin Dashboard
- Total staff count
- Staff who have/haven't posted marks
- Overall academic summary
- View all student queries
- Read-only access to all results
- Respond to student queries

### 👨‍🏫 Staff Dashboard
- Post marks for students (once-only enforcement)
- Cannot edit or delete marks after submission
- Dashboard showing:
  - Total students attended
  - Pass percentage
- View student queries (read-only)

### 👨‍🎓 Student Dashboard
- View own marks and results
- Overall statistics (percentage, grades, pass/fail)
- Raise academic queries/complaints
- View query status and admin responses

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3
- **Architecture**: MVC-inspired structure

## Installation & Setup

### Prerequisites
- XAMPP/WAMP/LAMP (Apache + MySQL + PHP)
- Web browser
- MySQL database

### Step 1: Clone/Download Project
```bash
git clone <repository-url>
# OR
# Download and extract the project folder
```

### Step 2: Database Setup

1. **Start MySQL Server** (via XAMPP/WAMP control panel)

2. **Create Database**:
   - Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
   - Create a new database named `student_result_system`
   - Or use MySQL command line:
     ```sql
     CREATE DATABASE student_result_system;
     ```

3. **Import Schema**:
   - Open `database/schema.sql` file
   - Copy all SQL statements
   - Execute in phpMyAdmin SQL tab or via command line:
     ```bash
     mysql -u root -p student_result_system < database/schema.sql
     ```
   - This will create all tables and insert sample data

### Step 3: Configure Database Connection

Edit `config/database.php` and update database credentials if needed:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Change if different
define('DB_PASS', '');          // Add password if set
define('DB_NAME', 'student_result_system');
```

### Step 4: Configure Base URL

Edit `config/config.php` and update the BASE_URL to match your setup:

```php
// For XAMPP with project in htdocs folder:
define('BASE_URL', 'http://localhost/Student-Result-Management-System-1/');

// OR if using virtual host:
// define('BASE_URL', 'http://your-domain.local/');
```

### Step 5: Access the Application

1. **Start Apache** (via XAMPP/WAMP control panel)

2. **Open Browser** and navigate to:
   ```
   http://localhost/Student-Result-Management-System-1/
   ```

## Default Login Credentials

### Admin
- **Username**: `admin`
- **Password**: `password`

### Staff
- **Username**: `staff1`, `staff2`, or `staff3`
- **Password**: `password`

### Student
- **Username**: `student1`, `student2`, `student3`, `student4`, or `student5`
- **Password**: `password`

## Project Structure

```
Student-Result-Management-System-1/
├── admin/
│   ├── dashboard.php          # Admin main dashboard
│   ├── view_query.php         # View and respond to queries
│   └── view_results.php       # View all results (read-only)
├── staff/
│   ├── dashboard.php          # Staff main dashboard
│   ├── post_marks.php         # Post marks for students
│   └── view_marks.php         # View posted marks
├── student/
│   ├── dashboard.php          # Student main dashboard
│   └── submit_query.php       # Submit query handler
├── assets/
│   └── css/
│       └── style.css          # Main stylesheet
├── config/
│   ├── config.php             # Main configuration
│   └── database.php           # Database connection
├── database/
│   └── schema.sql             # Database schema with sample data
├── includes/
│   ├── functions.php          # Helper functions
│   ├── header.php             # Common header
│   └── footer.php             # Common footer
├── index.php                  # Login page
├── logout.php                 # Logout handler
└── README.md                  # This file
```

## Database Schema

### Tables

1. **users**: Stores admin, staff, and student login credentials
2. **students**: Student information (linked to users via email)
3. **subjects**: Available subjects with total marks
4. **marks**: Student marks (enforced unique per student-subject)
5. **queries**: Student queries/complaints with admin responses

### Key Constraints

- **UNIQUE constraint** on `marks(student_id, subject_id)` - prevents duplicate entries
- **Foreign keys** ensure referential integrity
- **CASCADE deletes** maintain data consistency

## Security Features

1. **Password Hashing**: Passwords stored using bcrypt (PHP's `password_hash()`)
2. **SQL Injection Prevention**: Prepared statements used throughout
3. **XSS Protection**: Input sanitization with `htmlspecialchars()`
4. **Session Management**: Secure session handling with role-based access
5. **One-time Mark Posting**: Database constraint + backend validation prevents mark modification

## Important Rules

### Staff Mark Posting
- Staff can post marks **ONLY ONCE** per subject/student combination
- Once submitted, marks **cannot be edited or deleted**
- Enforcement at both database level (UNIQUE constraint) and application level

### Admin Access
- Admin has **read-only** access to results
- Admin cannot post or modify marks
- Admin can only respond to queries

### Student Access
- Students can view **only their own** results
- Students can raise queries about their results
- Students cannot see other students' data

## Customization

### Adding New Subjects
Insert into `subjects` table:
```sql
INSERT INTO subjects (subject_code, subject_name, total_marks) 
VALUES ('MATH201', 'Advanced Mathematics', 100);
```

### Adding New Users
```sql
-- For staff
INSERT INTO users (username, password, role, full_name, email) 
VALUES ('newstaff', '$2y$10$...', 'staff', 'Staff Name', 'email@school.com');

-- For student (also need to add to students table)
INSERT INTO students (student_id, full_name, email, class) 
VALUES ('STU006', 'Student Name', 'email@school.com', 'Class 10A');
```

### Changing Password Hash
To generate a new password hash:
```php
echo password_hash('your_password', PASSWORD_BCRYPT);
```

## Troubleshooting

### Database Connection Error
- Check if MySQL is running
- Verify credentials in `config/database.php`
- Ensure database `student_result_system` exists

### Page Not Found
- Verify BASE_URL in `config/config.php`
- Check Apache is running
- Ensure project folder is in correct location (htdocs for XAMPP)

### Session Issues
- Check PHP session directory is writable
- Verify `session_start()` is called in `config/config.php`
- Clear browser cookies and try again

### Marks Not Saving
- Check database UNIQUE constraint isn't being violated
- Verify staff has permission to post for that subject
- Check PHP error logs for detailed error messages

## Development Notes

- **Password**: All demo accounts use password `password` (bcrypt hashed)
- **Session Timeout**: Sessions persist until logout (can be configured)
- **Error Handling**: Basic error handling included; extend as needed
- **Responsive Design**: CSS includes mobile-responsive breakpoints

## Future Enhancements

- Email notifications for query responses
- Export results to PDF/Excel
- Bulk mark upload via CSV
- Grade calculation improvements
- Attendance tracking integration
- Advanced reporting and analytics

## License

This project is open-source and available for educational purposes.

## Support

For issues or questions:
1. Check this README
2. Review PHP error logs
3. Check database connection and schema
4. Verify file permissions

---

**Developed with ❤️ for Academic Excellence**

