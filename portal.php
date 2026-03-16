<?php
session_start();
require_once('db_config.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: db_index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? "Architect";

// Fetch all projects (including those "deleted" by admin)
$query = "SELECT * FROM projects WHERE user_id = '$user_id' ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DraftBoard | My Workspace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f5f5dc; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #8b795e; padding-bottom: 10px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        
        /* Normal Project Card */
        .project-card { 
            background: white; padding: 20px; border-radius: 12px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;
            transition: 0.3s;
        }
        .project-card:hover { transform: translateY(-5px); }

        /* Admin Deleted Styling */
        .deleted-card { 
            background: #fff5f5; border: 2px dashed #f87171; 
            opacity: 0.8; position: relative; overflow: hidden;
        }
        .admin-notice { color: #b91c1c; font-size: 12px; font-weight: bold; text-transform: uppercase; display: flex; align-items: center; gap: 5px; }
        
        .btn-view { display: inline-block; margin-top: 15px; color: #8b795e; text-decoration: none; font-weight: 600; font-size: 14px; }
        .logout-btn { color: #b91c1c; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="header">
    <h1>Welcome, <?php echo htmlspecialchars($user_name); ?></h1>
    <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<div class="grid">
    <?php while($p = mysqli_fetch_assoc($result)): ?>
        
        <?php if ($p['deleted_by_admin'] == 1): ?>
            <div class="project-card deleted-card">
                <div class="admin-notice">
                    <i class="fa-solid fa-circle-exclamation"></i> Access Revoked
                </div>
                <h3 style="margin: 10px 0; color: #7f1d1d;"><?php echo htmlspecialchars($p['project_name']); ?></h3>
                <p style="font-size: 13px; color: #991b1b;">This project was deleted by the System Admin and is no longer editable.</p>
            </div>

        <?php else: ?>
            <div class="project-card">
                <h3 style="margin: 0; color: #433422;"><?php echo htmlspecialchars($p['project_name']); ?></h3>
                <p style="color: #64748b; font-size: 13px;">Project ID: #<?php echo $p['id']; ?></p>
                <a href="editor.php?id=<?php echo $p['id']; ?>" class="btn-view">Open Workflow →</a>
            </div>
        <?php endif; ?>

    <?php endwhile; ?>
</div>

<?php if(mysqli_num_rows($result) == 0): ?>
    <div style="text-align: center; margin-top: 100px; color: #8b795e;">
        <i class="fa-regular fa-folder-open fa-3x"></i>
        <p>No active workflows found. Start your first project!</p>
    </div>
<?php endif; ?>

</body>
</html>