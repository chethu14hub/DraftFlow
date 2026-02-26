<?php
include('db_config.php');
session_start();

$message = "";

// --- PHP LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's a Registration or Login request
    if (isset($_POST['action']) && $_POST['action'] == 'register') {
        $user = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (username, email, password) VALUES ('$user', '$email', '$pass')";
        if (mysqli_query($conn, $sql)) {
            $message = "<span style='color: #38bdf8;'>Architect Registered! Please Login.</span>";
        } else {
            $message = "Error: Email might already exist.";
        }
    } 
    
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass = $_POST['password'];
        
        $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        $row = mysqli_fetch_assoc($result);
        
        if ($row && password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "<span style='color: #f87171;'>Access Denied. Invalid Credentials.</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DraftBoard | Secure Access</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Modern Architecture Blueprint Theme */
        body {
            margin: 0; height: 100vh; display: flex; align-items: center; justify-content: center;
            background: #0f172a; font-family: 'Segoe UI', sans-serif;
            background-image: linear-gradient(rgba(56, 189, 248, 0.05) 1px, transparent 1px), 
                              linear-gradient(90deg, rgba(56, 189, 248, 0.05) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .auth-card {
            width: 380px; padding: 40px; border-radius: 20px; color: white; text-align: center;
            background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        .icon-header { font-size: 45px; color: #38bdf8; margin-bottom: 15px; }
        
        h2 { font-weight: 300; letter-spacing: 3px; margin-bottom: 5px; }
        .subtitle { color: #94a3b8; font-size: 12px; margin-bottom: 30px; letter-spacing: 1px; }

        input {
            width: 100%; padding: 12px; margin: 10px 0; background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: white; outline: none;
        }

        button {
            width: 100%; padding: 12px; margin-top: 20px; background: #38bdf8; border: none;
            border-radius: 8px; color: #0f172a; font-weight: bold; cursor: pointer; transition: 0.3s;
        }

        button:hover { background: #0ea5e9; transform: scale(1.02); }

        .toggle-link { margin-top: 20px; font-size: 14px; color: #94a3b8; cursor: pointer; }
        .toggle-link span { color: #38bdf8; font-weight: bold; }

        .hidden { display: none; }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="icon-header"><i class="fa-solid fa-compass-drafting"></i></div>
    <h2>DRAFTBOARD</h2>
    <div class="subtitle">PROJECT ARCHITECT PORTAL</div>
    
    <p><?php echo $message; ?></p>

    <form id="login-form" method="POST" action="auth.php">
        <input type="hidden" name="action" value="login">
        <input type="email" name="email" placeholder="Architect Email" required>
        <input type="password" name="password" placeholder="Passcode" required>
        <button type="submit">ACCESS SYSTEM</button>
        <div class="toggle-link" onclick="toggleForm()">New Architect? <span>Register here</span></div>
    </form>

    <form id="register-form" class="hidden" method="POST" action="auth.php">
        <input type="hidden" name="action" value="register">
        <input type="text" name="username" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Work Email" required>
        <input type="password" name="password" placeholder="Set Passcode" required>
        <button type="submit">INITIALIZE ARCHITECT</button>
        <div class="toggle-link" onclick="toggleForm()">Already registered? <span>Login here</span></div>
    </form>
</div>

<script>
    function toggleForm() {
        document.getElementById('login-form').classList.toggle('hidden');
        document.getElementById('register-form').classList.toggle('hidden');
    }
</script>

</body>
</html>