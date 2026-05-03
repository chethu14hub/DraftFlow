<?php
session_start();
require_once('db_config.php');

// Security: Only Admin can be here
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: db_index.php");
    exit();
}

// Fetch all users and count their projects
$query = "SELECT users.id, users.username, users.email, COUNT(projects.id) as project_count 
          FROM users 
          LEFT JOIN projects ON users.id = projects.user_id 
          GROUP BY users.id";
$result = mysqli_query($conn, $query);

// Get Total Stats
$total_users = mysqli_num_rows($result);
$total_projects_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM projects");
$total_projects = mysqli_fetch_assoc($total_projects_res)['total'];

// NEW: Get Feedback Count
$feedback_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM feedback");
$total_feedback = mysqli_fetch_assoc($feedback_count_res)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DraftBoard | System Oversight</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bronze: #8b795e; --dark: #1e293b; --bg: #f8fafc; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; color: var(--dark); }
        
        .admin-nav { background: #fff; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .logo { font-weight: 800; font-size: 20px; letter-spacing: 1px; }
        
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        /* Stats Cards - Updated to 4 columns to fit Feedback */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; }
        .stat-card h3 { margin: 0; color: #64748b; font-size: 14px; text-transform: uppercase; }
        .stat-card p { font-size: 28px; font-weight: 800; margin: 10px 0 0 0; color: var(--bronze); }

        /* Table Style */
        .user-table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }
        .user-table th { background: #f1f5f9; padding: 15px; text-align: left; font-size: 13px; color: #64748b; }
        .user-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; }
        .user-table tr:hover { background: #f8fafc; }

        .btn-inspect { 
            background: var(--dark); color: white; padding: 8px 16px; border-radius: 6px; 
            text-decoration: none; font-size: 13px; transition: 0.3s;
        }
        .btn-inspect:hover { background: var(--bronze); }
        
        /* New Feedback Button Style */
        .btn-feedback {
            background: #f59e0b; color: white; padding: 10px 20px; border-radius: 8px;
            text-decoration: none; font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px;
            transition: 0.3s;
        }
        .btn-feedback:hover { background: #d97706; transform: translateY(-2px); }
        
        .badge { background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body>

<nav class="admin-nav">
    <div class="logo"><i class="fa-solid fa-shield-halved"></i> SYSTEM <span>OVERSIGHT</span></div>
    <div style="display: flex; gap: 20px; align-items: center;">
        <!-- NEW: Feedback Portal Button -->
        <a href="admin_feedback.php" class="btn-feedback">
            <i class="fa-solid fa-comment-dots"></i> User Feedback
        </a>
        <span class="badge">Admin: <?php echo $_SESSION['user_name']; ?></span>
        <a href="logout.php" style="color: #ef4444; text-decoration: none; font-weight: 700;">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Registered Architects</h3>
            <p><?php echo $total_users; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Active Flows</h3>
            <p><?php echo $total_projects; ?></p>
        </div>
        <!-- NEW: Feedback Stat Card -->
        <div class="stat-card">
            <h3>User Feedback</h3>
            <p><?php echo $total_feedback; ?></p>
        </div>
        <div class="stat-card">
            <h3>System Status</h3>
            <?php if ($conn): ?>
                <p style="color: #22c55e;"><i class="fa-solid fa-circle-check"></i> Operational</p>
            <?php else: ?>
                <p style="color: #ef4444;"><i class="fa-solid fa-circle-xmark"></i> Database Offline</p>
            <?php endif; ?>
        </div>
    </div>

    <table class="user-table">
        <thead>
            <tr>
                <th>Architect Name</th>
                <th>Email Address</th>
                <th>Flows Created</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($row['username']); ?></strong></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><span class="badge"><?php echo $row['project_count']; ?> Projects</span></td>
                <td style="text-align: right;">
                    <a href="admin_inspect.php?view_user=<?php echo $row['id']; ?>" class="btn-inspect">
                        <i class="fa-solid fa-eye"></i> Inspect Workflow
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>