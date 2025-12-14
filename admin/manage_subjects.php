<?php
/**
 * Admin: Manage Subjects (Add/Remove)
 */
require_once '../config/config.php';
requireRole('admin');

$conn = getDBConnection();

// Handle add subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $subject_code = sanitizeInput($_POST['subject_code'] ?? '');
    $subject_name = sanitizeInput($_POST['subject_name'] ?? '');
    $total_marks = intval($_POST['total_marks'] ?? 100);
    
    if (!empty($subject_code) && !empty($subject_name)) {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, total_marks) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $subject_code, $subject_name, $total_marks);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Subject added successfully!');
        } else {
            setFlashMessage('error', 'Failed to add subject: ' . $conn->error);
        }
        $stmt->close();
    } else {
        setFlashMessage('error', 'Please fill in all required fields.');
    }
    header('Location: manage_subjects.php');
    exit();
}

// Handle delete subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $subject_id = intval($_POST['subject_id'] ?? 0);
    
    if ($subject_id > 0) {
        // Check if subject has marks
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM marks WHERE subject_id = ?");
        $checkStmt->bind_param("i", $subject_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $checkStmt->close();
        
        if ($count > 0) {
            setFlashMessage('error', 'Cannot delete subject. It has marks associated with it.');
        } else {
            $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->bind_param("i", $subject_id);
            
            if ($stmt->execute()) {
                setFlashMessage('success', 'Subject removed successfully!');
            } else {
                setFlashMessage('error', 'Failed to remove subject.');
            }
            $stmt->close();
        }
    }
    header('Location: manage_subjects.php');
    exit();
}

// Get all subjects
$subjects = [];
$result = $conn->query("SELECT * FROM subjects ORDER BY subject_code");
while ($row = $result->fetch_assoc()) {
    // Count marks for each subject
    $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM marks WHERE subject_id = ?");
    $countStmt->bind_param("i", $row['id']);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $row['marks_count'] = $countResult->fetch_assoc()['count'];
    $countStmt->close();
    
    $subjects[] = $row;
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">Manage Subjects</h1>
        <?php displayFlashMessage(); ?>
        
        <!-- Add Subject Form -->
        <div class="dashboard-section">
            <h2>Add New Subject</h2>
            <form method="POST" action="" class="subject-form">
                <input type="hidden" name="action" value="add">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="subject_code">Subject Code *</label>
                        <input type="text" id="subject_code" name="subject_code" required placeholder="e.g., MATH101">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject_name">Subject Name *</label>
                        <input type="text" id="subject_name" name="subject_name" required placeholder="e.g., Mathematics">
                    </div>
                    
                    <div class="form-group">
                        <label for="total_marks">Total Marks</label>
                        <input type="number" id="total_marks" name="total_marks" value="100" min="1" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Subject</button>
            </form>
        </div>
        
        <!-- Subjects List -->
        <div class="dashboard-section">
            <h2>All Subjects</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Total Marks</th>
                            <th>Marks Entered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subjects)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No subjects found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subjects as $subject): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                    <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                    <td><?php echo $subject['total_marks']; ?></td>
                                    <td><?php echo $subject['marks_count']; ?> records</td>
                                    <td>
                                        <?php if ($subject['marks_count'] > 0): ?>
                                            <span class="text-muted">Cannot delete (has marks)</span>
                                        <?php else: ?>
                                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this subject?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
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

