<?php
session_start();

// --- STEP 1: SILENT CONNECTION CHECK ---
// We check for the file first so the PHP Warning doesn't break your beautiful UI
$db_status = false;
if (file_exists('db_config.php')) {
    require_once('db_config.php');
    $db_status = true;
}

$message = "";

// --- STEP 2: PHP LOGIC FOR LOGIN & REGISTER ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && $db_status) {
    
    // REGISTRATION LOGIC
    if (isset($_POST['action']) && $_POST['action'] == 'register') {
        $user = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
        
        $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if(mysqli_num_rows($check_email) > 0) {
            $message = "<span style='color: #991b1b;'>Email already in system.</span>";
        } else {
            $sql = "INSERT INTO users (username, email, password) VALUES ('$user', '$email', '$pass')";
            if (mysqli_query($conn, $sql)) {
                $message = "<span style='color: #433422;'>Account Initialized! Please Login.</span>";
            }
        }
    } 
    
    // LOGIN LOGIC
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
            $message = "<span style='color: #991b1b;'>Invalid Credentials.</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DraftBoard | Architect Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- STEP 3: THE CREAM & VELLUM UI --- */
        body {
            margin: 0; height: 100vh; display: flex; align-items: center; justify-content: center;
            background-color: #f5f5dc; /* Cream */
            background-image: linear-gradient(rgba(139, 121, 94, 0.1) 1px, transparent 1px), 
                              linear-gradient(90deg, rgba(139, 121, 94, 0.1) 1px, transparent 1px);
            background-size: 30px 30px; /* Blueprint Grid */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .auth-card {
            width: 380px; padding: 40px; border-radius: 12px; text-align: center;
            background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px);
            border: 1px solid rgba(139, 121, 94, 0.2);
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        }

        .db-error {
            background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 5px;
            font-size: 13px; margin-bottom: 20px; border: 1px solid #fecaca;
        }

        .icon-header { font-size: 50px; color: #8b795e; margin-bottom: 10px; }
        h2 { color: #2d2419; letter-spacing: 4px; font-weight: 300; margin: 0; }
        .subtitle { color: #8b795e; font-size: 11px; letter-spacing: 2px; margin-bottom: 30px; }

        input {
            width: 100%; padding: 12px; margin: 10px 0; background: #fff;
            border: 1px solid #dcdcdc; border-radius: 4px; outline: none; box-sizing: border-box;
        }

        button {
            width: 100%; padding: 12px; margin-top: 20px; background: #433422; border: none;
            border-radius: 4px; color: #f5f5dc; font-weight: bold; cursor: pointer; transition: 0.3s;
        }

        button:hover { background: #5c4a33; letter-spacing: 1px; }

        .toggle-link { margin-top: 25px; font-size: 13px; color: #7a7a7a; cursor: pointer; }
        .toggle-link span { color: #8b795e; font-weight: bold; text-decoration: underline; }

        .hidden { display: none; }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="icon-header"><i class="fa-solid fa-compass-drafting"></i></div>
    <h2>DRAFTBOARD</h2>
    <div class="subtitle">VISUAL ARCHITECTURE ENGINE</div>

    <?php if (!$db_status): ?>
        <div class="db-error">
            <i class="fa-solid fa-triangle-exclamation"></i> 
            Bridge Missing: Create <strong>db_config.php</strong> in the root folder.
        </div>
    <?php endif; ?>

    <p style="font-size: 14px;"><?php echo $message; ?></p>

    <form id="login-form" method="POST" action="">
        <input type="hidden" name="action" value="login">
        <input type="email" name="email" placeholder="Architect Email" required>
        <input type="password" name="password" placeholder="Passcode" required>
        <button type="submit" <?php if(!$db_status) echo 'disabled style="opacity:0.5"'; ?>>ACCESS SYSTEM</button>
        <div class="toggle-link" onclick="toggle()">New Architect? <span>Initialize Here</span></div>
    </form>

    <form id="register-form" class="hidden" method="POST" action="">
        <input type="hidden" name="action" value="register">
        <input type="text" name="username" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Professional Email" required>
        <input type="password" name="password" placeholder="Set Passcode" required>
        <button type="submit" <?php if(!$db_status) echo 'disabled style="opacity:0.5"'; ?>>CREATE CREDENTIALS</button>
        <div class="toggle-link" onclick="toggle()">Already Registered? <span>Login Here</span></div>
    </form>
</div>

<script>
    function toggle() {
        document.getElementById('login-form').classList.toggle('hidden');
        document.getElementById('register-form').classList.toggle('hidden');
    }
</script>

</body>
</html>