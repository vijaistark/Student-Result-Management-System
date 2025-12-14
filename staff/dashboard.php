<?php
/**
 * Staff Dashboard
 * Can post marks ONLY ONCE per subject/student
 * Cannot edit or delete marks once submitted
 * Shows dashboard summary: total students attended, pass percentage
 * Can view student queries (read-only)
 */
require_once '../config/config.php';
requireRole('staff');

$conn = getDBConnection();
$staff_id = $_SESSION['user_id'];

// Get statistics
$stats = [];

// Total students attended (students for whom this staff has posted marks)
$result = $conn->query("SELECT COUNT(DISTINCT student_id) as total FROM marks WHERE staff_id = $staff_id");
$stats['students_attended'] = $result->fetch_assoc()['total'];

// Calculate pass percentage
$result = $conn->query("
    SELECT 
        COUNT(*) as total_records,
        COUNT(CASE WHEN (m.marks_obtained / sub.total_marks * 100) >= 40 THEN 1 END) as pass_count
    FROM marks m
    INNER JOIN subjects sub ON m.subject_id = sub.id
    WHERE m.staff_id = $staff_id
");
$pass_data = $result->fetch_assoc();
$stats['pass_percentage'] = $pass_data['total_records'] > 0 
    ? round(($pass_data['pass_count'] / $pass_data['total_records']) * 100, 2) 
    : 0;

// Get subjects assigned to this staff (subjects for which they've posted marks)
$subjects_posted = [];
$result = $conn->query("
    SELECT DISTINCT sub.id, sub.subject_code, sub.subject_name, sub.total_marks
    FROM subjects sub
    INNER JOIN marks m ON sub.id = m.subject_id
    WHERE m.staff_id = $staff_id
");
while ($row = $result->fetch_assoc()) {
    $subjects_posted[] = $row;
}

// Get all subjects (for posting new marks)
$all_subjects = [];
$result = $conn->query("SELECT id, subject_code, subject_name, total_marks FROM subjects ORDER BY subject_code");
while ($row = $result->fetch_assoc()) {
    $all_subjects[] = $row;
}

// Get student queries related to subjects this staff teaches
$queries = [];
$query = "
    SELECT q.*, s.full_name as student_name, s.student_id, sub.subject_name 
    FROM queries q
    LEFT JOIN students s ON q.student_id = s.id
    LEFT JOIN subjects sub ON q.subject_id = sub.id
    WHERE q.subject_id IN (SELECT DISTINCT subject_id FROM marks WHERE staff_id = $staff_id)
    OR q.subject_id IS NULL
    ORDER BY q.created_at DESC
    LIMIT 10
";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $queries[] = $row;
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Student Result Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">Staff Dashboard</h1>
        <?php displayFlashMessage(); ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue">
                    <span>👥</span>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['students_attended']; ?></h3>
                    <p>Students Attended</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">
                    <span>📊</span>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['pass_percentage']; ?>%</h3>
                    <p>Pass Percentage</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-icon-purple">
                    <span>📚</span>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($subjects_posted); ?></h3>
                    <p>Subjects Posted</p>
                </div>
            </div>
        </div>
        
        <!-- Post Marks Section -->
        <div class="dashboard-section">
            <h2>Post Marks</h2>
            <p class="info-text">⚠️ You can post marks ONLY ONCE per subject/student. Once submitted, marks cannot be edited or deleted.</p>
            
            <form method="GET" action="post_marks.php" class="inline-form">
                <div class="form-group">
                    <label for="subject_id">Select Subject</label>
                    <select id="subject_id" name="subject_id" required>
                        <option value="">-- Select Subject --</option>
                        <?php foreach ($all_subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>">
                                <?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Post Marks for Subject</button>
            </form>
        </div>
        
        <!-- Posted Marks Summary -->
        <?php if (!empty($subjects_posted)): ?>
        <div class="dashboard-section">
            <h2>Posted Marks Summary</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Students</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects_posted as $subject): ?>
                            <?php
                            // Count students for this subject
                            $conn = getDBConnection();
                            $countResult = $conn->query("SELECT COUNT(DISTINCT student_id) as count FROM marks WHERE staff_id = $staff_id AND subject_id = {$subject['id']}");
                            $student_count = $countResult->fetch_assoc()['count'];
                            closeDBConnection($conn);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?></td>
                                <td><?php echo $student_count; ?> students</td>
                                <td>
                                    <a href="view_marks.php?subject_id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-info">View Marks</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Student Queries -->
        <div class="dashboard-section">
            <h2>Student Queries (Read-Only)</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Query</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($queries)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No queries found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($queries as $query): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($query['student_name']); ?> (<?php echo $query['student_id']; ?>)</td>
                                    <td><?php echo $query['subject_name'] ?? 'General'; ?></td>
                                    <td><?php echo htmlspecialchars($query['query_type']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($query['query_text'], 0, 50)); ?>...</td>
                                    <td><?php echo getQueryStatusBadge($query['status']); ?></td>
                                    <td><?php echo formatDate($query['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

