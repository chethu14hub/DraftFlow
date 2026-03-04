<?php
session_start();
require_once('db_config.php');

// Security Check: Kick out if not a User
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}

$u_id = $_SESSION['user_id'];
$query = "SELECT * FROM user_designs WHERE user_id = '$u_id' ORDER BY created_at DESC";
$projects = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>DraftBoard | My Studio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { margin: 0; display: flex; background: #f5f5dc; font-family: 'Segoe UI', sans-serif; height: 100vh; }
        .sidebar { width: 280px; background: #433422; color: #f5f5dc; padding: 30px; }
        .main-stage { flex: 1; padding: 40px; overflow-y: auto; }
        .project-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 30px; }
        .card { background: white; padding: 20px; border-radius: 12px; border: 1px solid rgba(139,121,94,0.3); transition: 0.3s; cursor: pointer; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .new-btn { background: #8b795e; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>DRAFTBOARD</h2>
        <p>Architect: <strong><?php echo $_SESSION['username']; ?></strong></p>
        <hr style="opacity:0.2; margin: 20px 0;">
        <p><i class="fa-solid fa-layer-group"></i> My Workflows</p>
        <p><i class="fa-solid fa-clock-rotate-left"></i> Recent Activity</p>
        <br>
        <a href="index.php?logout=1" style="color: #fca5a5; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Exit Studio</a>
    </div>

    <div class="main-stage">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h1>My Design Workspace</h1>
            <button class="new-btn">+ Create New Workflow</button>
        </div>

        <div class="project-grid">
            <?php if(mysqli_num_rows($projects) > 0): ?>
                <?php while($p = mysqli_fetch_assoc($projects)): ?>
                    <div class="card">
                        <i class="fa-solid fa-diagram-project" style="color:#8b795e; font-size: 24px;"></i>
                        <h3 style="margin: 15px 0 5px 0;"><?php echo $p['design_name']; ?></h3>
                        <span style="font-size: 11px; color: #94a3b8;"><?php echo $p['created_at']; ?></span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align:center; padding: 100px; color: #8b795e;">
                    <i class="fa-solid fa-pen-ruler fa-3x"></i>
                    <p>No workflows found. Time to start your first draft!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>