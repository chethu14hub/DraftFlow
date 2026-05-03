<?php
session_start();
// Include your existing database configuration
require_once('db_config.php');

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize input data to prevent SQL injection
    $name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);

    // Insert the feedback into the database
    $sql = "INSERT INTO feedback (user_name, rating, description) 
            VALUES ('$name', '$rating', '$desc')";
    
    if (mysqli_query($conn, $sql)) {
        // Success: Alert the user and return to the portal
        echo "<script>
                alert('Feedback successfully sent to the Admin! Thank you for your input.');
                window.location.href='portal.php';
              </script>";
    } else {
        // Error handling
        echo "Database Error: " . mysqli_error($conn);
    }
} else {
    // If someone tries to access this file directly without submitting the form
    header("Location: portal.php");
    exit();
}
?>