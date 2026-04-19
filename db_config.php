<?php
<<<<<<< HEAD
$host = "localhost";
$user = "root";   // Default XAMPP user
$pass = "";       // Default XAMPP password is empty
$dbname = "draftboard_db"; // The name we gave our database

// Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
=======
// 🔹 Load Composer (for dotenv)
require_once __DIR__ . '/vendor/autoload.php';

// 🔹 Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 🔹 Database credentials (can stay or move to .env later)
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "draftboard_db";

// 🔹 Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// 🔹 Check connection
>>>>>>> c631a520ebf19dbf936074dbab1d7ccc72e9f24f
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>