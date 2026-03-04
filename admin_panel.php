<?php
session_start();
require_once('db_config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get all users who are architects
$users_query = "SELECT * FROM users WHERE role = 'user'";
$users_result = mysqli_query($conn, $users_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>DraftBoard | Master Oversight</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f5f5dc; padding: 50px; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 40px; border-radius: 15px; border: 1px solid #8b795e; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th, td { padding: 18px; text-align: left; border-bottom: 1px solid #f1f1f1; }
        th { background: #433422; color: #f5f5dc; font-weight: 300; letter-spacing: 1px; }
        .action-link { color: #8b795e; font-weight: bold; text-decoration: none; border: 1px solid #8b795e; padding: 6px 15px; border-radius: 4px; transition: 0.3s; }
        .action-link:hover { background: #8b795e; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h1><i class="fa-solid fa-user-shield"></i> System Oversight</h1>
            <a href="index.php?logout=1" style="color: #991b1b; text-decoration:none;">Logout</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Architect Name</th>
                    <th>Email Address</th>
                    <th>Projects</th>
                    <th>Workflow Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($users_result)): 
                    $u_id = $row['id'];
                    $p_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM user_designs WHERE user_id = '$u_id'");
                    $p_count = mysqli_fetch_assoc($p_count_res)['total'];
                ?>
                <tr>
                    <td><strong><?php echo $row['username']; ?></strong></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><span style="background:#f5f5dc; padding: 4px 10px; border-radius: 20px;"><?php echo $p_count; ?></span></td>
                    <td>
                        <a href="view_user_workflow.php?id=<?php echo $u_id; ?>" class="action-link">Inspect Workflow</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>