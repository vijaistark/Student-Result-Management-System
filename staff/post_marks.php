<?php
/**
 * Post Marks Page
 * Staff can post marks for students in a specific subject
 * Enforces "post only once" rule at both database and backend level
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
    setFlashMessage('error', 'Invalid subject selected.');
    header('Location: dashboard.php');
    exit();
}

// Get all students
$students = [];
$result = $conn->query("SELECT id, student_id, full_name FROM students ORDER BY student_id");
while ($row = $result->fetch_assoc()) {
    // Check if marks already exist for this student-subject combination
    $checkStmt = $conn->prepare("SELECT id FROM marks WHERE student_id = ? AND subject_id = ?");
    $checkStmt->bind_param("ii", $row['id'], $subject_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $row['has_marks'] = $checkResult->num_rows > 0;
    $checkStmt->close();
    
    $students[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marks_data = $_POST['marks'] ?? [];
    $errors = [];
    $success_count = 0;
    
    foreach ($marks_data as $student_id => $marks_obtained) {
        // Validate marks
        if ($marks_obtained === '' || $marks_obtained === null) {
            continue; // Skip empty entries
        }
        
        $marks_obtained = floatval($marks_obtained);
        
        if ($marks_obtained < 0 || $marks_obtained > $subject['total_marks']) {
            $errors[] = "Marks for student ID $student_id must be between 0 and {$subject['total_marks']}";
            continue;
        }
        
        // Check if marks already exist (double-check to enforce "post only once" rule)
        $checkStmt = $conn->prepare("SELECT id FROM marks WHERE student_id = ? AND subject_id = ?");
        $checkStmt->bind_param("ii", $student_id, $subject_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $errors[] = "Marks for student ID $student_id in this subject already exist. Cannot modify.";
            $checkStmt->close();
            continue;
        }
        $checkStmt->close();
        
        // Insert marks
        $insertStmt = $conn->prepare("INSERT INTO marks (student_id, subject_id, staff_id, marks_obtained) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("iiid", $student_id, $subject_id, $staff_id, $marks_obtained);
        
        if ($insertStmt->execute()) {
            $success_count++;
        } else {
            // Check if error is due to duplicate entry (UNIQUE constraint)
            if ($conn->errno === 1062) {
                $errors[] = "Marks for student ID $student_id already exist (duplicate entry prevented).";
            } else {
                $errors[] = "Failed to insert marks for student ID $student_id: " . $conn->error;
            }
        }
        
        $insertStmt->close();
    }
    
    if ($success_count > 0) {
        setFlashMessage('success', "Successfully posted marks for $success_count student(s).");
    }
    
    if (!empty($errors)) {
        setFlashMessage('error', implode('<br>', $errors));
    }
    
    header('Location: dashboard.php');
    exit();
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Marks - Staff Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 class="page-title">Post Marks</h1>
        <?php displayFlashMessage(); ?>
        
        <div class="info-box">
            <h3><?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?></h3>
            <p><strong>Total Marks:</strong> <?php echo $subject['total_marks']; ?></p>
            <p class="warning-text">⚠️ You can post marks ONLY ONCE per student. Once submitted, marks cannot be edited or deleted.</p>
        </div>
        
        <form method="POST" action="" class="marks-form">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Marks Obtained</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo $student['student_id']; ?></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td>
                                    <?php if ($student['has_marks']): ?>
                                        <span class="badge badge-success">Already Posted</span>
                                        <input type="hidden" name="marks[<?php echo $student['id']; ?>]" value="">
                                    <?php else: ?>
                                        <input 
                                            type="number" 
                                            name="marks[<?php echo $student['id']; ?>]" 
                                            min="0" 
                                            max="<?php echo $subject['total_marks']; ?>" 
                                            step="0.01"
                                            placeholder="Enter marks"
                                            class="marks-input"
                                        >
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($student['has_marks']): ?>
                                        <span class="text-muted">Cannot modify</span>
                                    <?php else: ?>
                                        <span class="text-info">Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit Marks</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>

