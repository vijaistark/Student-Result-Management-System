<?php
/**
 * View All Results (Admin can edit/delete)
 * Admin can view, edit, and delete all student results
 */
require_once '../config/config.php';
requireRole('admin');

$conn = getDBConnection();

// Handle edit mark
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $mark_id = intval($_POST['mark_id'] ?? 0);
    $marks_obtained = floatval($_POST['marks_obtained'] ?? 0);
    
    if ($mark_id > 0) {
        // Get subject total marks
        $stmt = $conn->prepare("SELECT sub.total_marks FROM marks m INNER JOIN subjects sub ON m.subject_id = sub.id WHERE m.id = ?");
        $stmt->bind_param("i", $mark_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        
        if ($data && $marks_obtained >= 0 && $marks_obtained <= $data['total_marks']) {
            $updateStmt = $conn->prepare("UPDATE marks SET marks_obtained = ? WHERE id = ?");
            $updateStmt->bind_param("di", $marks_obtained, $mark_id);
            
            if ($updateStmt->execute()) {
                setFlashMessage('success', 'Marks updated successfully!');
            } else {
                setFlashMessage('error', 'Failed to update marks.');
            }
            $updateStmt->close();
        } else {
            setFlashMessage('error', 'Invalid marks value.');
        }
    }
    header('Location: view_results.php' . (isset($_GET['sort']) ? '?sort=' . $_GET['sort'] . '&order=' . $_GET['order'] : ''));
    exit();
}

// Handle delete mark
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $mark_id = intval($_POST['mark_id'] ?? 0);
    
    if ($mark_id > 0) {
        $stmt = $conn->prepare("DELETE FROM marks WHERE id = ?");
        $stmt->bind_param("i", $mark_id);
        
        if ($stmt->execute()) {
            setFlashMessage('success', 'Marks deleted successfully!');
        } else {
            setFlashMessage('error', 'Failed to delete marks.');
        }
        $stmt->close();
    }
    header('Location: view_results.php' . (isset($_GET['sort']) ? '?sort=' . $_GET['sort'] . '&order=' . $_GET['order'] : ''));
    exit();
}

// Sorting
$sort_by = $_GET['sort'] ?? 'student_id';
$sort_order = strtoupper($_GET['order'] ?? 'ASC');
$allowed_sort = ['student_id', 'student_name', 'subject_name', 'marks_obtained', 'percentage', 'grade', 'posted_at'];
$sort_by = in_array($sort_by, $allowed_sort) ? $sort_by : 'student_id';
$sort_order = ($sort_order === 'ASC' || $sort_order === 'DESC') ? $sort_order : 'ASC';

// Build ORDER BY clause
$order_by = "s.student_id";
switch ($sort_by) {
    case 'student_name':
        $order_by = "s.full_name";
        break;
    case 'subject_name':
        $order_by = "sub.subject_name";
        break;
    case 'marks_obtained':
        $order_by = "m.marks_obtained";
        break;
    case 'percentage':
        $order_by = "(m.marks_obtained / sub.total_marks * 100)";
        break;
    case 'grade':
        $order_by = "(m.marks_obtained / sub.total_marks * 100)";
        break;
    case 'posted_at':
        $order_by = "m.posted_at";
        break;
}

// Get all results with student and subject details
$results = [];
$query = "
    SELECT 
        m.*,
        s.student_id,
        s.full_name as student_name,
        sub.subject_code,
        sub.subject_name,
        sub.total_marks,
        u.full_name as staff_name
    FROM marks m
    INNER JOIN students s ON m.student_id = s.id
    INNER JOIN subjects sub ON m.subject_id = sub.id
    INNER JOIN users u ON m.staff_id = u.id
    ORDER BY {$order_by} {$sort_order}
";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $row['percentage'] = calculatePercentage($row['marks_obtained'], $row['total_marks']);
    $row['status'] = getPassFailStatus($row['marks_obtained'], $row['total_marks']);
    $row['grade'] = getGrade($row['percentage']);
    $results[] = $row;
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View All Results - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">Manage Results</h1>
        <?php displayFlashMessage(); ?>
        
        <!-- Sort Options -->
        <div class="dashboard-section" style="margin-bottom: 20px;">
            <h3>Sort Results</h3>
            <form method="GET" action="" style="display: flex; gap: 10px; align-items: flex-end;">
                <div class="form-group" style="flex: 1;">
                    <label for="sort">Sort By</label>
                    <select id="sort" name="sort" style="width: 100%;">
                        <option value="student_id" <?php echo $sort_by === 'student_id' ? 'selected' : ''; ?>>Student ID</option>
                        <option value="student_name" <?php echo $sort_by === 'student_name' ? 'selected' : ''; ?>>Student Name</option>
                        <option value="subject_name" <?php echo $sort_by === 'subject_name' ? 'selected' : ''; ?>>Subject</option>
                        <option value="marks_obtained" <?php echo $sort_by === 'marks_obtained' ? 'selected' : ''; ?>>Marks</option>
                        <option value="percentage" <?php echo $sort_by === 'percentage' ? 'selected' : ''; ?>>Percentage</option>
                        <option value="grade" <?php echo $sort_by === 'grade' ? 'selected' : ''; ?>>Grade</option>
                        <option value="posted_at" <?php echo $sort_by === 'posted_at' ? 'selected' : ''; ?>>Date</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="order">Order</label>
                    <select id="order" name="order" style="width: 100%;">
                        <option value="ASC" <?php echo $sort_order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                        <option value="DESC" <?php echo $sort_order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Sort</button>
            </form>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Subject</th>
                        <th>Marks Obtained</th>
                        <th>Total Marks</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                        <th>Status</th>
                        <th>Posted By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="11" class="text-center">No results found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?php echo $result['student_id']; ?></td>
                                <td><?php echo htmlspecialchars($result['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                                <td>
                                    <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Update marks?');">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="mark_id" value="<?php echo $result['id']; ?>">
                                        <input type="number" name="marks_obtained" value="<?php echo $result['marks_obtained']; ?>" 
                                               min="0" max="<?php echo $result['total_marks']; ?>" step="0.01" 
                                               style="width: 80px;" required>
                                        <button type="submit" class="btn btn-sm btn-info" title="Update">✓</button>
                                    </form>
                                </td>
                                <td><?php echo $result['total_marks']; ?></td>
                                <td><?php echo $result['percentage']; ?>%</td>
                                <td><strong><?php echo $result['grade']; ?></strong></td>
                                <td>
                                    <?php if ($result['status'] === 'Pass'): ?>
                                        <span class="badge badge-success">Pass</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Fail</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($result['staff_name']); ?></td>
                                <td><?php echo formatDate($result['posted_at']); ?></td>
                                <td>
                                    <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this result?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="mark_id" value="<?php echo $result['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="form-actions" style="margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

