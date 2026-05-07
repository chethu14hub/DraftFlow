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

// --- MANUAL .ENV PARSER ---
// This section reads the .env file and loads variables into $_ENV
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) continue;
        
        // Split by the first '='
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

// Define the variable for the chatbot to use
$groq_api_key = $_ENV['GROQ_API_KEY'] ?? '';

// Check for session to prevent errors on some servers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>