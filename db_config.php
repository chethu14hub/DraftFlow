<?php
$host = "localhost";
$user = "root";   // Default XAMPP user
$pass = "";       // Default XAMPP password is empty
$dbname = "draftboard_db"; // The name we gave our database

// Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>