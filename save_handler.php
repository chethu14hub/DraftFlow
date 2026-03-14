<?php
session_start();
require_once('db_config.php');

// Security check
if (!isset($_SESSION['user_id']) || !isset($_POST['project_name'])) {
    echo "Error: Unauthorized or missing data";
    exit();
}

$user_id = $_SESSION['user_id'];
$project_name = mysqli_real_escape_string($conn, $_POST['project_name']);
$project_data = mysqli_real_escape_string($conn, $_POST['diagram']);

// Check if project already exists for this user
$check_query = "SELECT id FROM projects WHERE user_id = '$user_id' AND project_name = '$project_name'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) > 0) {
    // Update existing project
    $sql = "UPDATE projects SET project_data = '$project_data', updated_at = NOW() 
            WHERE user_id = '$user_id' AND project_name = '$project_name'";
} else {
    // Insert new project
    $sql = "INSERT INTO projects (user_id, project_name, project_data, updated_at) 
            VALUES ('$user_id', '$project_name', '$project_data', NOW())";
}

if (mysqli_query($conn, $sql)) {
    echo "Success";
} else {
    echo "Database Error: " . mysqli_error($conn);
}
?>