<?php
/**
 * View All Results (Read-only for Admin)
 * Admin can view all student results but cannot edit
 */
require_once '../config/config.php';
requireRole('admin');

$conn = getDBConnection();

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
    ORDER BY s.student_id, sub.subject_code
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
        <h1 class="page-title">All Results (Read-Only)</h1>
        <?php displayFlashMessage(); ?>
        
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
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="10" class="text-center">No results found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?php echo $result['student_id']; ?></td>
                                <td><?php echo htmlspecialchars($result['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                                <td><?php echo number_format($result['marks_obtained'], 2); ?></td>
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
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

