<?php
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
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>