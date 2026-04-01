<?php
session_start();
require_once('db_config.php');

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access.");
}

if (isset($_POST['delete_project'])) {
    $project_id = mysqli_real_escape_string($conn, $_POST['project_id']);
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);

    // Update the project to mark it as deleted by admin
    $query = "UPDATE projects SET deleted_by_admin = 1 WHERE id = '$project_id'";
    
    if (mysqli_query($conn, $query)) {
        // Redirect back to the inspection page for that user
        header("Location: admin_inspect.php?view_user=" . $user_id . "&status=deleted");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>