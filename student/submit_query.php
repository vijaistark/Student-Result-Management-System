<?php
/**
 * Submit Query Handler
 * Processes student query submissions
 */
require_once '../config/config.php';
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

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
$subject_id = !empty($_POST['subject_id']) ? intval($_POST['subject_id']) : null;
$query_type = sanitizeInput($_POST['query_type'] ?? '');
$query_text = sanitizeInput($_POST['query_text'] ?? '');

if (empty($query_type) || empty($query_text)) {
    setFlashMessage('error', 'Please fill in all required fields.');
    header('Location: dashboard.php');
    exit();
}

// Insert query
$stmt = $conn->prepare("INSERT INTO queries (student_id, subject_id, query_type, query_text, status) VALUES (?, ?, ?, ?, 'pending')");
$stmt->bind_param("iiss", $student_id, $subject_id, $query_type, $query_text);

if ($stmt->execute()) {
    setFlashMessage('success', 'Query submitted successfully. You will be notified once it is reviewed.');
} else {
    setFlashMessage('error', 'Failed to submit query. Please try again.');
}

$stmt->close();
closeDBConnection($conn);

header('Location: dashboard.php');
exit();
?>

