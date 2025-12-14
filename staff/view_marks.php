<?php
/**
 * View Posted Marks
 * Staff can view marks they have posted (read-only)
 */
require_once '../config/config.php';
requireRole('staff');

$subject_id = $_GET['subject_id'] ?? 0;
$conn = getDBConnection();
$staff_id = $_SESSION['user_id'];

// Get subject details
$stmt = $conn->prepare("SELECT id, subject_code, subject_name, total_marks FROM subjects WHERE id = ?");
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$result = $stmt->get_result();
$subject = $result->fetch_assoc();
$stmt->close();

if (!$subject) {
    setFlashMessage('error', 'Invalid subject.');
    header('Location: dashboard.php');
    exit();
}

// Sorting
$sort_by = $_GET['sort'] ?? 'student_id';
$sort_order = strtoupper($_GET['order'] ?? 'ASC');
$allowed_sort = ['student_id', 'student_name', 'marks_obtained', 'percentage', 'grade', 'posted_at'];
$sort_by = in_array($sort_by, $allowed_sort) ? $sort_by : 'student_id';
$sort_order = ($sort_order === 'ASC' || $sort_order === 'DESC') ? $sort_order : 'ASC';

// Build ORDER BY clause
$total_marks = $subject['total_marks'];
$order_by = "s.student_id";
switch ($sort_by) {
    case 'student_name':
        $order_by = "s.full_name";
        break;
    case 'marks_obtained':
        $order_by = "m.marks_obtained";
        break;
    case 'percentage':
        $order_by = "(m.marks_obtained / {$total_marks} * 100)";
        break;
    case 'grade':
        $order_by = "(m.marks_obtained / {$total_marks} * 100)";
        break;
    case 'posted_at':
        $order_by = "m.posted_at";
        break;
}

// Get marks posted by this staff for this subject
$marks = [];
$query = "
    SELECT 
        m.*,
        s.student_id,
        s.full_name as student_name
    FROM marks m
    INNER JOIN students s ON m.student_id = s.id
    WHERE m.subject_id = ? AND m.staff_id = ?
    ORDER BY {$order_by} {$sort_order}
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $subject_id, $staff_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['percentage'] = calculatePercentage($row['marks_obtained'], $subject['total_marks']);
    $row['status'] = getPassFailStatus($row['marks_obtained'], $subject['total_marks']);
    $row['grade'] = getGrade($row['percentage']);
    $marks[] = $row;
}
$stmt->close();
closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Marks - Staff Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">View Posted Marks</h1>
        <?php displayFlashMessage(); ?>
        
        <div class="info-box">
            <h3><?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?></h3>
            <p><strong>Total Marks:</strong> <?php echo $subject['total_marks']; ?></p>
            <p class="info-text">📋 This is a read-only view. Marks cannot be edited or deleted once posted.</p>
        </div>
        
        <!-- Sort Options -->
        <div style="margin-bottom: 15px;">
            <form method="GET" action="" style="display: flex; gap: 10px; align-items: flex-end;">
                <input type="hidden" name="subject_id" value="<?php echo $subject_id; ?>">
                <div class="form-group" style="flex: 1;">
                    <label for="sort">Sort By</label>
                    <select id="sort" name="sort" style="width: 100%;">
                        <option value="student_id" <?php echo $sort_by === 'student_id' ? 'selected' : ''; ?>>Student ID</option>
                        <option value="student_name" <?php echo $sort_by === 'student_name' ? 'selected' : ''; ?>>Student Name</option>
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
                        <th>Marks Obtained</th>
                        <th>Total Marks</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                        <th>Status</th>
                        <th>Posted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($marks)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No marks found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($marks as $mark): ?>
                            <tr>
                                <td><?php echo $mark['student_id']; ?></td>
                                <td><?php echo htmlspecialchars($mark['student_name']); ?></td>
                                <td><?php echo number_format($mark['marks_obtained'], 2); ?></td>
                                <td><?php echo $subject['total_marks']; ?></td>
                                <td><?php echo $mark['percentage']; ?>%</td>
                                <td><strong><?php echo $mark['grade']; ?></strong></td>
                                <td>
                                    <?php if ($mark['status'] === 'Pass'): ?>
                                        <span class="badge badge-success">Pass</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Fail</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatDateTime($mark['posted_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="form-actions">
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

