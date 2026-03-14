<?php
session_start();
require_once('db_config.php');
if (!isset($_SESSION['user_id'])) { header("Location: db_index.php"); exit(); }

$user_name = $_SESSION['user_name'] ?? "Chethan Y";
$user_id = $_SESSION['user_id'];

// Improved Query: Fetch projects belonging to the logged-in user
$query = "SELECT * FROM projects WHERE user_id = '$user_id' ORDER BY id DESC";
$result = mysqli_query($conn, $query);

$projects = [];
if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $projects[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DraftFlow | Professional Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --cream: #fdfcf0; --bronze: #8b795e; --dark: #1e293b; --slate: #64748b; }
        body { font-family: 'Inter', sans-serif; background: var(--cream); margin: 0; color: var(--dark); overflow-x: hidden; }
        
        .navbar { background: white; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .logo { font-weight: 800; font-size: 22px; color: var(--dark); letter-spacing: -1px; }
        .logo span { color: var(--bronze); }

        .container { max-width: 1100px; margin: 50px auto; padding: 0 20px; }

        .welcome-hero { 
            background: white; padding: 40px; border-radius: 20px; 
            border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.02);
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 40px;
        }

        .btn-create { 
            background: var(--bronze); color: white; padding: 14px 28px; 
            border-radius: 12px; text-decoration: none; font-weight: 700; 
            display: inline-flex; align-items: center; gap: 10px;
            transition: 0.3s; border: none; font-size: 16px; cursor: pointer;
        }
        .btn-create:hover { background: #6f5f4a; transform: translateY(-2px); }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }
        
        .project-card { 
            background: white; padding: 25px; border-radius: 16px; 
            border: 1px solid #e2e8f0; transition: 0.3s; cursor: pointer;
        }
        .project-card:hover { border-color: var(--bronze); box-shadow: 0 12px 20px rgba(0,0,0,0.05); }

        /* MODAL STYLES */
        #nameModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; }
        .modal-content { background:white; width:400px; margin:15% auto; padding:30px; border-radius:15px; text-align:center; }
        .modal-input { width:100%; padding:12px; margin:20px 0; border:1px solid #ddd; border-radius:8px; box-sizing: border-box; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo">Draft<span>Flow</span></div>
        <div style="display: flex; align-items: center; gap: 20px;">
            <span style="font-weight: 600;"><?php echo $user_name; ?></span>
            <a href="logout.php" style="color: #ef4444; text-decoration: none; font-weight: 600;">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-hero">
            <div class="hero-text">
                <h1>Hi, <?php echo explode(' ', $user_name)[0]; ?>!</h1>
                <p>Welcome to DraftFlow. What are we building today?</p>
            </div>
            <button onclick="document.getElementById('nameModal').style.display='block'" class="btn-create">
                <i class="fa-solid fa-plus"></i> Start New Flow
            </button>
        </div>

        <div class="grid">
            <?php if (empty($projects)): ?>
                <div style="grid-column: 1 / -1; text-align: center; color: var(--slate); padding: 50px;">
                    <i class="fa-solid fa-folder-open" style="font-size: 40px; margin-bottom: 10px;"></i>
                    <p>No projects saved yet. Create one to see it here!</p>
                </div>
            <?php else: ?>
                <?php foreach($projects as $p): ?>
                <div class="project-card" onclick="location.href='dashboard.php?project=<?php echo urlencode($p['project_name']); ?>'">
                    <i class="fa-solid fa-diagram-project" style="color: var(--bronze); font-size: 24px; margin-bottom: 15px;"></i>
                    <h3><?php echo htmlspecialchars($p['project_name']); ?></h3>
                    <p style="font-size: 12px; color: #999;">Created on: <?php echo date('M d, Y', strtotime($p['updated_at'])); ?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div id="nameModal">
        <div class="modal-content">
            <h3>Name your Architecture</h3>
            <input type="text" id="projNameInput" class="modal-input" placeholder="e.g., E-commerce Backend">
            <button onclick="startProject()" class="btn-create" style="width:100%; justify-content:center;">Create Project</button>
            <p onclick="document.getElementById('nameModal').style.display='none'" style="margin-top:15px; cursor:pointer; color:var(--slate); font-size:12px;">Cancel</p>
        </div>
    </div>

    <script>
        function startProject() {
            let name = document.getElementById('projNameInput').value;
            if(name.trim() === "") { alert("Please enter a name"); return; }
            location.href = "dashboard.php?new=true&project=" + encodeURIComponent(name);
        }
    </script>
</body>
</html>