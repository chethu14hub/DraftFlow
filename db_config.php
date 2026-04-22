<?php
// Smart Config: Works on XAMPP and Railway
if (getenv('MYSQLHOST')) {
    $host = getenv('MYSQLHOST');
    $user = getenv('MYSQLUSER');
    $pass = getenv('MYSQLPASSWORD');
    $db   = getenv('MYSQLDATABASE');
    $port = getenv('MYSQLPORT');
} else {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "draftboard_db";
    $port = "3306";
}

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Manually define the key here because your .env is gone
$groq_api_key =$_ENV['GROQ_API_KEY'];
?>