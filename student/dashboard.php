<?php
/**
 * Student Dashboard
 * Can view only their own marks
 * Can raise academic queries/complaints
 * Can view query status
 */
require_once '../config/config.php';
requireRole('student');

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get student record linked to user account
$stmt = $conn->prepare("SELECT id, student_id, full_name, email, class FROM students WHERE email = (SELECT email FROM users WHERE id = ?) LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    setFlashMessage('error', 'Student record not found. Please contact administrator.');
    header('Location: ../logout.php');
    exit();
}

$student_id = $student['id'];

// Get all marks for this student
$marks = [];
$query = "
    SELECT 
        m.*,
        sub.subject_code,
        sub.subject_name,
        sub.total_marks,
        u.full_name as staff_name
    FROM marks m
    INNER JOIN subjects sub ON m.subject_id = sub.id
    INNER JOIN users u ON m.staff_id = u.id
    WHERE m.student_id = ?
    ORDER BY sub.subject_code
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['percentage'] = calculatePercentage($row['marks_obtained'], $row['total_marks']);
    $row['status'] = getPassFailStatus($row['marks_obtained'], $row['total_marks']);
    $row['grade'] = getGrade($row['percentage']);
    $marks[] = $row;
}
$stmt->close();

// Calculate overall statistics
$total_marks_obtained = array_sum(array_column($marks, 'marks_obtained'));
$total_marks_possible = array_sum(array_column($marks, 'total_marks'));
$overall_percentage = $total_marks_possible > 0 ? calculatePercentage($total_marks_obtained, $total_marks_possible) : 0;
$pass_count = count(array_filter($marks, function($m) { return $m['status'] === 'Pass'; }));
$total_subjects = count($marks);
$pass_percentage = $total_subjects > 0 ? round(($pass_count / $total_subjects) * 100, 2) : 0;

// Get student's queries
$queries = [];
$query = "
    SELECT q.*, sub.subject_name 
    FROM queries q
    LEFT JOIN subjects sub ON q.subject_id = sub.id
    WHERE q.student_id = ?
    ORDER BY q.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $queries[] = $row;
}
$stmt->close();

// Get all subjects for query form
$all_subjects = [];
$result = $conn->query("SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_code");
while ($row = $result->fetch_assoc()) {
    $all_subjects[] = $row;
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Student Result Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">Student Dashboard</h1>
        <?php displayFlashMessage(); ?>
        
        <!-- Student Info -->
        <div class="student-info">
            <h2><?php echo htmlspecialchars($student['full_name']); ?></h2>
            <p><strong>Student ID:</strong> <?php echo $student['student_id']; ?> | 
               <strong>Class:</strong> <?php echo htmlspecialchars($student['class']); ?></p>
        </div>
        
        <!-- Overall Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue">
                    <span>📊</span>
                </div>
                <div class="stat-content">
                    <h3><?php echo $overall_percentage; ?>%</h3>
                    <p>Overall Percentage</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">
                    <span>✓</span>
                </div>
                <div class="stat-content">
                    <h3><?php echo $pass_count; ?>/<?php echo $total_subjects; ?></h3>
                    <p>Subjects Passed</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-icon-purple">
                    <span>📚</span>
                </div>
                <div class="stat-content">
                    <h3><?php echo $total_subjects; ?></h3>
                    <p>Subjects with Results</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon stat-icon-teal">
                    <span>🎓</span>
                </div>
                <div class="stat-content">
                    <h3><?php echo getGrade($overall_percentage); ?></h3>
                    <p>Overall Grade</p>
                </div>
            </div>
        </div>
        
        <!-- Marks Table -->
        <div class="dashboard-section">
            <h2>My Results</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Marks Obtained</th>
                            <th>Total Marks</th>
                            <th>Percentage</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Posted By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($marks)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No results available yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($marks as $mark): ?>
                                <tr>
                                    <td><?php echo $mark['subject_code']; ?></td>
                                    <td><?php echo htmlspecialchars($mark['subject_name']); ?></td>
                                    <td><?php echo number_format($mark['marks_obtained'], 2); ?></td>
                                    <td><?php echo $mark['total_marks']; ?></td>
                                    <td><?php echo $mark['percentage']; ?>%</td>
                                    <td><strong><?php echo $mark['grade']; ?></strong></td>
                                    <td>
                                        <?php if ($mark['status'] === 'Pass'): ?>
                                            <span class="badge badge-success">Pass</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Fail</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($mark['staff_name']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Raise Query Section -->
        <div class="dashboard-section">
            <h2>Raise a Query / Complaint</h2>
            <form method="POST" action="submit_query.php" class="query-form">
                <div class="form-group">
                    <label for="subject_id">Subject (Optional)</label>
                    <select id="subject_id" name="subject_id">
                        <option value="">-- General Query --</option>
                        <?php foreach ($all_subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>">
                                <?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="query_type">Query Type</label>
                    <select id="query_type" name="query_type" required>
                        <option value="">-- Select Type --</option>
                        <option value="Grade Discrepancy">Grade Discrepancy</option>
                        <option value="Clarification">Clarification</option>
                        <option value="General Query">General Query</option>
                        <option value="Complaint">Complaint</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="query_text">Query / Complaint Details</label>
                    <textarea id="query_text" name="query_text" rows="5" required placeholder="Please describe your query or complaint in detail..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Submit Query</button>
            </form>
        </div>
        
        <!-- My Queries -->
        <div class="dashboard-section">
            <h2>My Queries</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Query</th>
                            <th>Status</th>
                            <th>Response</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($queries)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No queries submitted yet</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($queries as $query): ?>
                                <tr>
                                    <td><?php echo $query['id']; ?></td>
                                    <td><?php echo $query['subject_name'] ?? 'General'; ?></td>
                                    <td><?php echo htmlspecialchars($query['query_type']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($query['query_text'], 0, 50)); ?>...</td>
                                    <td><?php echo getQueryStatusBadge($query['status']); ?></td>
                                    <td>
                                        <?php if ($query['admin_response']): ?>
                                            <?php echo htmlspecialchars(substr($query['admin_response'], 0, 50)); ?>...
                                        <?php else: ?>
                                            <span class="text-muted">No response yet</span>
                                        <?php endif; ?>
                                    </td>
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

