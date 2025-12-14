<?php
/**
 * Admin Dashboard
 * Shows statistics: total staff, staff who posted marks, staff who didn't
 * View all student queries
 * View overall academic summary
 */
require_once '../config/config.php';
requireRole('admin');

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total staff count
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'staff'");
$stats['total_staff'] = $result->fetch_assoc()['total'];

// Staff who have posted marks
$result = $conn->query("SELECT COUNT(DISTINCT staff_id) as count FROM marks");
$stats['staff_with_marks'] = $result->fetch_assoc()['count'];

// Staff who haven't posted marks
$stats['staff_without_marks'] = $stats['total_staff'] - $stats['staff_with_marks'];

// Total students
$result = $conn->query("SELECT COUNT(*) as total FROM students");
$stats['total_students'] = $result->fetch_assoc()['total'];

// Total subjects
$result = $conn->query("SELECT COUNT(*) as total FROM subjects");
$stats['total_subjects'] = $result->fetch_assoc()['total'];

// Total queries
$result = $conn->query("SELECT COUNT(*) as total FROM queries");
$stats['total_queries'] = $result->fetch_assoc()['total'];

// Pending queries
$result = $conn->query("SELECT COUNT(*) as total FROM queries WHERE status = 'pending'");
$stats['pending_queries'] = $result->fetch_assoc()['total'];

// Get all queries
$queries = [];
$result = $conn->query("
    SELECT q.*, s.full_name as student_name, s.student_id, sub.subject_name 
    FROM queries q
    LEFT JOIN students s ON q.student_id = s.id
    LEFT JOIN subjects sub ON q.subject_id = sub.id
    ORDER BY q.created_at DESC
    LIMIT 10
");
while ($row = $result->fetch_assoc()) {
    $queries[] = $row;
}

// Overall academic summary
$academic_summary = [];
$result = $conn->query("
    SELECT 
        COUNT(DISTINCT m.student_id) as students_attended,
        COUNT(m.id) as total_records,
        AVG(m.marks_obtained) as avg_marks,
        COUNT(CASE WHEN (m.marks_obtained / (SELECT total_marks FROM subjects WHERE id = m.subject_id) * 100) >= 40 THEN 1 END) as pass_count
    FROM marks m
");
$academic_summary = $result->fetch_assoc();
$academic_summary['pass_percentage'] = $academic_summary['total_records'] > 0 
    ? round(($academic_summary['pass_count'] / $academic_summary['total_records']) * 100, 2) 
    : 0;

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Student Result Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">Admin Dashboard</h1>
        <?php displayFlashMessage(); ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue">
                    <span>👥</span>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_staff']; ?></h3>
                    <p>Total Staff</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">
                    <span>✓</span>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['staff_with_marks']; ?></h3>
                    <p>Staff Posted Marks</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-icon-orange">
                    <span>⏱</span>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['staff_without_marks']; ?></h3>
                    <p>Staff Not Posted Marks</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-icon-purple">
                    <span>📚</span>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_students']; ?></h3>
                    <p>Total Students</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-icon-teal">
                    <span>📖</span>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_subjects']; ?></h3>
                    <p>Total Subjects</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-icon-red">
                    <span>❓</span>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_queries']; ?></h3>
                    <p>Total Queries</p>
                    <small><?php echo $stats['pending_queries']; ?> pending</small>
                </div>
            </div>
        </div>
        
        <!-- Academic Summary -->
        <div class="dashboard-section">
            <h2>Overall Academic Summary</h2>
            <div class="summary-grid">
                <div class="summary-card">
                    <h3>Students Attended</h3>
                    <p class="summary-value"><?php echo $academic_summary['students_attended']; ?></p>
                </div>
                <div class="summary-card">
                    <h3>Total Records</h3>
                    <p class="summary-value"><?php echo $academic_summary['total_records']; ?></p>
                </div>
                <div class="summary-card">
                    <h3>Average Marks</h3>
                    <p class="summary-value"><?php echo number_format($academic_summary['avg_marks'], 2); ?></p>
                </div>
                <div class="summary-card">
                    <h3>Pass Percentage</h3>
                    <p class="summary-value"><?php echo $academic_summary['pass_percentage']; ?>%</p>
                </div>
            </div>
        </div>
        
        <!-- Student Queries -->
        <div class="dashboard-section">
            <h2>Student Queries</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Query</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($queries)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No queries found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($queries as $query): ?>
                                <tr>
                                    <td><?php echo $query['id']; ?></td>
                                    <td><?php echo htmlspecialchars($query['student_name']); ?> (<?php echo $query['student_id']; ?>)</td>
                                    <td><?php echo $query['subject_name'] ?? 'General'; ?></td>
                                    <td><?php echo htmlspecialchars($query['query_type']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($query['query_text'], 0, 50)); ?>...</td>
                                    <td><?php echo getQueryStatusBadge($query['status']); ?></td>
                                    <td><?php echo formatDate($query['created_at']); ?></td>
                                    <td>
                                        <a href="view_query.php?id=<?php echo $query['id']; ?>" class="btn btn-sm btn-info">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- View All Results Link -->
        <div class="dashboard-section">
            <h2>Results Overview</h2>
            <p>View all student results (Read-only access)</p>
            <a href="view_results.php" class="btn btn-primary">View All Results</a>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

