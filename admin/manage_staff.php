<?php
/**
 * Admin: Manage Staff (Add Staff)
 */
require_once '../config/config.php';
requireRole('admin');

$conn = getDBConnection();

// Handle add staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    
    if (!empty($username) && !empty($password) && !empty($full_name)) {
        // Check if username already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            setFlashMessage('error', 'Username already exists. Please choose a different username.');
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, full_name, email) VALUES (?, ?, 'staff', ?, ?)");
            $stmt->bind_param("ssss", $username, $password_hash, $full_name, $email);
            
            if ($stmt->execute()) {
                setFlashMessage('success', 'Staff member added successfully!');
            } else {
                setFlashMessage('error', 'Failed to add staff member: ' . $conn->error);
            }
            $stmt->close();
        }
        $checkStmt->close();
    } else {
        setFlashMessage('error', 'Please fill in all required fields.');
    }
    header('Location: manage_staff.php');
    exit();
}

// Handle delete staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $staff_id = intval($_POST['staff_id'] ?? 0);
    
    if ($staff_id > 0) {
        // Check if staff has posted marks
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM marks WHERE staff_id = ?");
        $checkStmt->bind_param("i", $staff_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $checkStmt->close();
        
        if ($count > 0) {
            setFlashMessage('error', 'Cannot delete staff member. They have posted marks.');
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'staff'");
            $stmt->bind_param("i", $staff_id);
            
            if ($stmt->execute()) {
                setFlashMessage('success', 'Staff member removed successfully!');
            } else {
                setFlashMessage('error', 'Failed to remove staff member.');
            }
            $stmt->close();
        }
    }
    header('Location: manage_staff.php');
    exit();
}

// Get all staff
$staff = [];
$result = $conn->query("SELECT u.*, COUNT(DISTINCT m.id) as marks_posted FROM users u LEFT JOIN marks m ON u.id = m.staff_id WHERE u.role = 'staff' GROUP BY u.id ORDER BY u.full_name");
while ($row = $result->fetch_assoc()) {
    $staff[] = $row;
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">Manage Staff</h1>
        <?php displayFlashMessage(); ?>
        
        <!-- Add Staff Form -->
        <div class="dashboard-section">
            <h2>Add New Staff Member</h2>
            <form method="POST" action="" class="staff-form">
                <input type="hidden" name="action" value="add">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Staff Member</button>
            </form>
        </div>
        
        <!-- Staff List -->
        <div class="dashboard-section">
            <h2>All Staff Members</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Marks Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($staff)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No staff members found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($staff as $member): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($member['username']); ?></td>
                                    <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($member['email'] ?? '-'); ?></td>
                                    <td><?php echo $member['marks_posted']; ?> records</td>
                                    <td>
                                        <?php if ($member['marks_posted'] > 0): ?>
                                            <span class="text-muted">Cannot delete (has posted marks)</span>
                                        <?php else: ?>
                                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this staff member?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="staff_id" value="<?php echo $member['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                            </form>
                                        <?php endif; ?>
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

