<?php
session_start();
require_once('db_config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: db_index.php"); exit(); }

$view_id = $_GET['view_user'];

// Fetch projects for this specific user
$query = "SELECT * FROM projects WHERE user_id = '$view_id'";
$res = mysqli_query($conn, $query);

// Fetch user name for the title
$user_res = mysqli_query($conn, "SELECT username FROM users WHERE id = '$view_id'");
$user_data = mysqli_fetch_assoc($user_res);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inspecting: <?php echo $user_data['username']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: sans-serif; background: #f1f5f9; padding: 40px; }
        .project-card { 
            background: white; padding: 20px; border-radius: 10px; margin-bottom: 10px;
            display: flex; justify-content: space-between; align-items: center;
            border: 1px solid #e2e8f0;
        }
        .btn-delete { background: #fee2e2; color: #b91c1c; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
        .btn-delete:hover { background: #f87171; color: white; }
    </style>
</head>
<body>
    <h2>User Workflow: <?php echo $user_data['username']; ?></h2>
    <a href="admin_panel.php">← Back to Oversight</a>
    <hr>

    <?php while($p = mysqli_fetch_assoc($res)): ?>
    <div class="project-card">
        <div>
            <strong><?php echo $p['project_name']; ?></strong><br>
            <small>ID: #<?php echo $p['id']; ?></small>
        </div>
        <form method="POST" action="admin_actions.php" onsubmit="return confirm('Delete this project? User will be notified.');">
            <input type="hidden" name="project_id" value="<?php echo $p['id']; ?>">
            <input type="hidden" name="user_id" value="<?php echo $view_id; ?>">
            <button type="submit" name="delete_project" class="btn-delete">
                <i class="fa-solid fa-trash"></i> Delete Project
            </button>
        </form>
    </div>
    <?php endwhile; ?>
</body>
</html>