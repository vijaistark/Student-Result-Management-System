<?php
/**
 * View Query Details
 * Admin can view and respond to student queries
 */
require_once '../config/config.php';
requireRole('admin');

$query_id = $_GET['id'] ?? 0;
$conn = getDBConnection();

// Get query details
$stmt = $conn->prepare("
    SELECT q.*, s.full_name as student_name, s.student_id, sub.subject_name 
    FROM queries q
    LEFT JOIN students s ON q.student_id = s.id
    LEFT JOIN subjects sub ON q.subject_id = sub.id
    WHERE q.id = ?
");
$stmt->bind_param("i", $query_id);
$stmt->execute();
$result = $stmt->get_result();
$query = $result->fetch_assoc();

if (!$query) {
    setFlashMessage('error', 'Query not found.');
    header('Location: dashboard.php');
    exit();
}

// Handle response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = sanitizeInput($_POST['status'] ?? '');
    $response = sanitizeInput($_POST['response'] ?? '');
    
    $updateStmt = $conn->prepare("UPDATE queries SET status = ?, admin_response = ? WHERE id = ?");
    $updateStmt->bind_param("ssi", $status, $response, $query_id);
    
    if ($updateStmt->execute()) {
        setFlashMessage('success', 'Query updated successfully.');
        header('Location: dashboard.php');
        exit();
    } else {
        setFlashMessage('error', 'Failed to update query.');
    }
    
    $updateStmt->close();
}

$stmt->close();
closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Query - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">View Query</h1>
        <?php displayFlashMessage(); ?>
        
        <div class="query-details">
            <div class="detail-card">
                <h3>Query Information</h3>
                <div class="detail-row">
                    <strong>Student:</strong> <?php echo htmlspecialchars($query['student_name']); ?> (<?php echo $query['student_id']; ?>)
                </div>
                <div class="detail-row">
                    <strong>Subject:</strong> <?php echo $query['subject_name'] ?? 'General'; ?>
                </div>
                <div class="detail-row">
                    <strong>Type:</strong> <?php echo htmlspecialchars($query['query_type']); ?>
                </div>
                <div class="detail-row">
                    <strong>Status:</strong> <?php echo getQueryStatusBadge($query['status']); ?>
                </div>
                <div class="detail-row">
                    <strong>Date:</strong> <?php echo formatDateTime($query['created_at']); ?>
                </div>
                <div class="detail-row">
                    <strong>Query:</strong>
                    <p class="query-text"><?php echo nl2br(htmlspecialchars($query['query_text'])); ?></p>
                </div>
                <?php if ($query['admin_response']): ?>
                    <div class="detail-row">
                        <strong>Admin Response:</strong>
                        <p class="response-text"><?php echo nl2br(htmlspecialchars($query['admin_response'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="detail-card">
                <h3>Respond to Query</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="pending" <?php echo $query['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="reviewed" <?php echo $query['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                            <option value="resolved" <?php echo $query['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="response">Admin Response</label>
                        <textarea id="response" name="response" rows="5" placeholder="Enter your response..."><?php echo htmlspecialchars($query['admin_response'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Query</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

