<?php
/**
 * Header Component
 * Common header for all pages
 */
if (!isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$current_role = $_SESSION['role'];
$full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
?>
<header class="main-header">
    <div class="header-content">
        <div class="logo">
            <h1>📚 Result Management System</h1>
        </div>
        <nav class="main-nav">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($full_name); ?></span>
                <span class="user-role"><?php echo ucfirst($current_role); ?></span>
            </div>
            <a href="../logout.php" class="btn btn-sm btn-logout">Logout</a>
        </nav>
    </div>
</header>

