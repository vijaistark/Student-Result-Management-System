<?php
/**
 * Cancel/Delete Query Handler
 * Students can cancel their own queries
 */
require_once '../config/config.php';
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$query_id = intval($_POST['query_id'] ?? 0);

if ($query_id <= 0) {
    setFlashMessage('error', 'Invalid query ID.');
    header('Location: dashboard.php');
    exit();
}

// Get student record
$stmt = $conn->prepare("SELECT id FROM students WHERE email = (SELECT email FROM users WHERE id = ?) LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    setFlashMessage('error', 'Student record not found.');
    header('Location: dashboard.php');
    exit();
}

$student_id = $student['id'];

// Verify query belongs to this student and delete
$stmt = $conn->prepare("DELETE FROM queries WHERE id = ? AND student_id = ?");
$stmt->bind_param("ii", $query_id, $student_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        setFlashMessage('success', 'Query cancelled successfully.');
    } else {
        setFlashMessage('error', 'Query not found or you do not have permission to cancel it.');
    }
} else {
    setFlashMessage('error', 'Failed to cancel query.');
}

$stmt->close();
closeDBConnection($conn);

header('Location: dashboard.php');
exit();
?>

