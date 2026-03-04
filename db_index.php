<?php
session_start();

// --- STEP 1: SILENT CONNECTION CHECK ---
$db_status = false;
if (file_exists('db_config.php')) {
    require_once('db_config.php');
    $db_status = true;
}

$message = "";

// --- STEP 2: PHP LOGIC FOR ALL LOGINS ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2A. ADMIN LOGIN (Hardcoded - No DB Needed)
    if (isset($_POST['action']) && $_POST['action'] == 'admin_login') {
        $admin_user = "admin@draftboard.com";
        $admin_pass = "admin123"; // You can change this later

        if ($_POST['email'] === $admin_user && $_POST['password'] === $admin_pass) {
            $_SESSION['user_id'] = 'ADMIN_001';
            $_SESSION['username'] = 'Head Architect';
            $_SESSION['role'] = 'admin';
            header("Location: admin_panel.php");
            exit();
        } else {
            $message = "<span style='color: #991b1b;'>Invalid Admin Credentials.</span>";
        }
    }

    // 2B. USER REGISTRATION (Requires DB)
    if (isset($_POST['action']) && $_POST['action'] == 'register' && $db_status) {
        $user = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
        
        $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if(mysqli_num_rows($check_email) > 0) {
            $message = "<span style='color: #991b1b;'>Error: Email already in system.</span>";
        } else {
            $sql = "INSERT INTO users (username, email, password, role) VALUES ('$user', '$email', '$pass', 'user')";
            if (mysqli_query($conn, $sql)) {
                $message = "<span style='color: #433422; font-weight:bold;'>Success! Please Login.</span>";
            }
        }
    } 
    
    // 2C. USER LOGIN (Requires DB)
    if (isset($_POST['action']) && $_POST['action'] == 'login' && $db_status) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass = $_POST['password'];
        
        $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        $row = mysqli_fetch_assoc($result);
        
        if ($row && password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = 'user';
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "<span style='color: #991b1b;'>Access Denied. Invalid Credentials.</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DraftBoard | Portal</title>
    <style>
        body {
            margin: 0; height: 100vh; display: flex; align-items: center; justify-content: center;
            background-color: #f5f5dc; 
            background-image: linear-gradient(rgba(139, 121, 94, 0.08) 1px, transparent 1px), 
                              linear-gradient(90deg, rgba(139, 121, 94, 0.08) 1px, transparent 1px);
            background-size: 35px 35px;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }

        .auth-card {
            width: 380px; padding: 50px 40px; border-radius: 12px; text-align: center;
            background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(12px);
            border: 1px solid rgba(139, 121, 94, 0.2);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.4s ease-in-out;
        }

        .auth-card:hover { transform: translateY(-8px); box-shadow: 0 25px 50px rgba(139, 121, 94, 0.15); background: rgba(255, 255, 255, 0.8); }

        h2 { color: #2d2419; letter-spacing: 6px; font-weight: 300; margin: 0; font-size: 26px; }
        .subtitle { color: #8b795e; font-size: 10px; letter-spacing: 3px; margin-bottom: 30px; text-transform: uppercase; margin-top: 5px; }

        input {
            width: 100%; padding: 13px; margin: 10px 0; background: #fff;
            border: 1px solid #dcdcdc; border-radius: 4px; outline: none; box-sizing: border-box;
        }

        button {
            width: 100%; padding: 14px; margin-top: 20px; background: #433422; border: none;
            border-radius: 4px; color: #f5f5dc; font-weight: bold; cursor: pointer; 
            transition: 0.3s; letter-spacing: 1px;
        }

        button:hover { background: #5c4a33; letter-spacing: 2px; }

        .admin-btn { background: transparent; color: #8b795e; border: 1px solid #8b795e; margin-top: 10px; font-size: 12px; }
        .admin-btn:hover { background: rgba(139, 121, 94, 0.1); color: #433422; }

        .toggle-link { margin-top: 25px; font-size: 13px; color: #7a7a7a; cursor: pointer; }
        .toggle-link span { color: #8b795e; font-weight: bold; text-decoration: underline; }

        .hidden { display: none; }
        .db-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; font-size: 12px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="auth-card">
    <h2>DRAFTBOARD</h2>
    <div id="form-title" class="subtitle">ARCHITECTURE ENGINE</div>

    <?php if (!$db_status): ?>
        <div class="db-error">User Database Offline - Only Admin Access Enabled</div>
    <?php endif; ?>

    <div style="margin-bottom: 15px; font-size: 14px; min-height: 20px; color: #433422;"><?php echo $message; ?></div>

    <form id="login-form" method="POST" action="">
        <input type="hidden" name="action" value="login">
        <input type="email" name="email" placeholder="Architect Email" required>
        <input type="password" name="password" placeholder="Passcode" required>
        <button type="submit" <?php if(!$db_status) echo 'disabled style="opacity:0.4"'; ?>>ACCESS SYSTEM</button>
        <div class="toggle-link" onclick="showForm('register-form', 'NEW INITIALIZATION')">New Architect? <span>Initialize</span></div>
        <button type="button" class="admin-btn" onclick="showForm('admin-form', 'ADMINISTRATOR ACCESS')">Admin Login</button>
    </form>

    <form id="register-form" class="hidden" method="POST" action="">
        <input type="hidden" name="action" value="register">
        <input type="text" name="username" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Work Email" required>
        <input type="password" name="password" placeholder="Set Passcode" required>
        <button type="submit" <?php if(!$db_status) echo 'disabled style="opacity:0.4"'; ?>>CREATE ACCOUNT</button>
        <div class="toggle-link" onclick="showForm('login-form', 'ARCHITECTURE ENGINE')">Back to <span>User Login</span></div>
    </form>

    <form id="admin-form" class="hidden" method="POST" action="">
        <input type="hidden" name="action" value="admin_login">
        <input type="email" name="email" placeholder="Admin Email" required>
        <input type="password" name="password" placeholder="Master Key" required>
        <button type="submit" style="background: #2d2419;">VERIFY ADMIN</button>
        <div class="toggle-link" onclick="showForm('login-form', 'ARCHITECTURE ENGINE')">Back to <span>User Login</span></div>
    </form>
</div>

<script>
    function showForm(formId, subtitle) {
        // Hide all forms
        document.getElementById('login-form').classList.add('hidden');
        document.getElementById('register-form').classList.add('hidden');
        document.getElementById('admin-form').classList.add('hidden');
        
        // Show selected form
        document.getElementById(formId).classList.remove('hidden');
        
        // Update subtitle text
        document.getElementById('form-title').innerText = subtitle;
    }
</script>

</body>
</html>