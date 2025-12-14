<?php
/**
 * Admin: Manage Students (Add/Remove)
 */
require_once '../config/config.php';
requireRole('admin');

$conn = getDBConnection();

// Handle add student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $student_id = sanitizeInput($_POST['student_id'] ?? '');
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $class = sanitizeInput($_POST['class'] ?? '');
    
    if (!empty($student_id) && !empty($full_name) && !empty($email)) {
        $stmt = $conn->prepare("INSERT INTO students (student_id, full_name, email, phone, class) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $student_id, $full_name, $email, $phone, $class);
        
        if ($stmt->execute()) {
            // Create corresponding user account for student
            $password_hash = password_hash('password', PASSWORD_BCRYPT);
            $username = strtolower(str_replace(' ', '', $full_name));
            
            $userStmt = $conn->prepare("INSERT INTO users (username, password, role, full_name, email) VALUES (?, ?, 'student', ?, ?)");
            $userStmt->bind_param("ssss", $username, $password_hash, $full_name, $email);
            $userStmt->execute();
            $userStmt->close();
            
            setFlashMessage('success', 'Student added successfully!');
        } else {
            setFlashMessage('error', 'Failed to add student: ' . $conn->error);
        }
        $stmt->close();
    } else {
        setFlashMessage('error', 'Please fill in all required fields.');
    }
    header('Location: manage_students.php');
    exit();
}

// Handle delete student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $student_id = intval($_POST['student_id'] ?? 0);
    
    if ($student_id > 0) {
        // Get email to delete user account
        $stmt = $conn->prepare("SELECT email FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();
        
        // Delete student (cascade will delete marks and queries)
        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        
        if ($stmt->execute()) {
            // Delete user account
            if ($student) {
                $userStmt = $conn->prepare("DELETE FROM users WHERE email = ? AND role = 'student'");
                $userStmt->bind_param("s", $student['email']);
                $userStmt->execute();
                $userStmt->close();
            }
            
            setFlashMessage('success', 'Student removed successfully!');
        } else {
            setFlashMessage('error', 'Failed to remove student.');
        }
        $stmt->close();
    }
    header('Location: manage_students.php');
    exit();
}

// Get all students
$students = [];
$result = $conn->query("SELECT * FROM students ORDER BY student_id");
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">Manage Students</h1>
        <?php displayFlashMessage(); ?>
        
        <!-- Add Student Form -->
        <div class="dashboard-section">
            <h2>Add New Student</h2>
            <form method="POST" action="" class="student-form">
                <input type="hidden" name="action" value="add">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="student_id">Student ID *</label>
                        <input type="text" id="student_id" name="student_id" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="class">Class</label>
                    <input type="text" id="class" name="class" placeholder="e.g., Class 10A">
                </div>
                
                <button type="submit" class="btn btn-primary">Add Student</button>
            </form>
        </div>
        
        <!-- Students List -->
        <div class="dashboard-section">
            <h2>All Students</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Class</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No students found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($student['class'] ?? '-'); ?></td>
                                    <td>
                                        <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this student? This will also delete all their marks and queries.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="form-actions">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

