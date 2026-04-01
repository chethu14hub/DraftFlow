<?php
session_start();
require_once('db_config.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $recovery = mysqli_real_escape_string($conn, $_POST['recovery_key']);
    $new_pass = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    // Check if user exists with that recovery key
    $query = "SELECT id FROM users WHERE email='$email' AND recovery_key='$recovery'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $update = "UPDATE users SET password='$new_pass' WHERE email='$email'";
        if (mysqli_query($conn, $update)) {
            $message = "<span style='color: green;'>Passcode updated! <a href='db_index.php'>Login here</a></span>";
        }
    } else {
        $message = "<span style='color: red;'>Invalid Email or Recovery Key.</span>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>DraftBoard | Reset</title>
    <style>
        body { background: #f5f5dc; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .reset-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #433422; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="reset-card">
        <h2>Reset Passcode</h2>
        <p style="font-size: 12px; color: #666;">Enter details to authorize reset</p>
        <div style="margin-bottom: 15px;"><?php echo $message; ?></div>
        <form method="POST">
            <input type="email" name="email" placeholder="Your Registered Email" required>
            <input type="text" name="recovery_key" placeholder="Your Recovery Key (Default: DraftFlow123)" required>
            <input type="password" name="new_password" placeholder="New Passcode" required>
            <button type="submit">UPDATE PASSCODE</button>
        </form>
        <br>
        <a href="db_index.php" style="font-size: 12px; color: #8b795e;">Back to Login</a>
    </div>
</body>
</html>