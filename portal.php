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

// Fetch all projects for this user
$query = "SELECT * FROM projects WHERE user_id = '$user_id' ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// Pre-fetch projects into an array to use mysqli_num_rows correctly later
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
    <title>DraftFlow | My Workspace</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --cream: #fdfcf0; --bronze: #8b795e; --dark: #1e293b; --slate: #64748b; }
        body { font-family: 'Inter', sans-serif; background: var(--cream); margin: 0; color: var(--dark); overflow-x: hidden; }
        
        /* Navbar */
        .navbar { background: white; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; }
        .logo { font-weight: 800; font-size: 22px; color: var(--dark); letter-spacing: -1px; }
        .logo span { color: var(--bronze); }

        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }

        /* Welcome Hero Section */
        .welcome-hero { 
            background: white; padding: 40px; border-radius: 20px; 
            border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.02);
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 40px;
        }
        .hero-text h1 { margin: 0; font-size: 28px; }
        .hero-text p { color: var(--slate); margin: 10px 0 0; }

        .btn-create { 
            background: var(--bronze); color: white; padding: 14px 28px; 
            border-radius: 12px; text-decoration: none; font-weight: 700; 
            display: inline-flex; align-items: center; gap: 10px;
            transition: 0.3s; border: none; font-size: 16px; cursor: pointer;
        }
        .btn-create:hover { background: #6f5f4a; transform: translateY(-2px); }

        /* Grid & Cards */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }
        
        .project-card { 
            background: white; padding: 25px; border-radius: 16px; 
            border: 1px solid #e2e8f0; transition: 0.3s; cursor: pointer;
            position: relative;
        }
        .project-card:hover { border-color: var(--bronze); transform: translateY(-5px); box-shadow: 0 12px 20px rgba(0,0,0,0.05); }

        .deleted-card { background: #fff5f5; border: 2px dashed #f87171; cursor: not-allowed; }
        .admin-notice { color: #b91c1c; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; display: block; }
        
        .btn-view { display: inline-block; margin-top: 15px; color: var(--bronze); text-decoration: none; font-weight: 700; font-size: 14px; }

        /* Modal Styles */
        #nameModal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; backdrop-filter: blur(4px); }
        .modal-content { background:white; width:400px; margin:15% auto; padding:35px; border-radius:20px; text-align:center; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .modal-input { width: 100%; padding: 14px; margin: 20px 0; border: 1px solid #ddd; border-radius: 10px; font-size: 16px; outline: none; box-sizing: border-box; }

        /* --- NEW FEEDBACK STYLES --- */
        .feedback-trigger {
            position: fixed; bottom: 30px; right: 30px; background: var(--bronze);
            color: white; width: 60px; height: 60px; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            box-shadow: 0 10px 25px rgba(139, 121, 94, 0.4); cursor: pointer;
            transition: 0.3s; z-index: 1000; font-size: 24px;
        }
        .feedback-trigger:hover { transform: scale(1.1); background: #6f5f4a; }

        .feedback-panel {
            display: none; position: fixed; bottom: 100px; right: 30px;
            width: 350px; background: white; border: 1px solid #e2e8f0;
            border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            z-index: 1001; overflow: hidden; animation: slideIn 0.3s ease;
        }
        @keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .feedback-header { background: var(--bronze); color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .feedback-body { padding: 20px; }
        
        /* Star Rating CSS */
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: center; gap: 10px; margin-bottom: 15px; }
        .star-rating input { display: none; }
        .star-rating label { font-size: 25px; color: #ddd; cursor: pointer; transition: 0.2s; }
        .star-rating input:checked ~ label { color: #ffc107; }
        .star-rating label:hover, .star-rating label:hover ~ label { color: #ffca28; }
        
        .feedback-input { width: 100%; border: 1px solid #ddd; border-radius: 10px; padding: 12px; margin-bottom: 15px; font-family: inherit; resize: none; box-sizing: border-box; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo">Draft<span>Flow</span></div>
        <div style="display: flex; align-items: center; gap: 20px;">
            <span style="font-weight: 600;"><?php echo htmlspecialchars($user_name); ?></span>
            <a href="logout.php" style="color: #ef4444; text-decoration: none; font-weight: 600;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-hero">
            <div class="hero-text">
                <h1>Hi, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>!</h1>
                <p>Ready to blueprint your next software architecture?</p>
            </div>
            <button onclick="document.getElementById('nameModal').style.display='block'" class="btn-create">
                <i class="fa-solid fa-plus"></i> Start New Flow
            </button>
        </div>

        <div class="grid">
            <?php if (empty($projects)): ?>
                <div style="grid-column: 1 / -1; text-align: center; color: var(--slate); padding: 50px;">
                    <i class="fa-regular fa-folder-open fa-3x" style="margin-bottom: 15px; display: block;"></i>
                    <p>No projects found. Use the button above to start your first flow!</p>
                </div>
            <?php else: ?>
                <?php foreach($projects as $p): ?>
                    <?php if (isset($p['deleted_by_admin']) && $p['deleted_by_admin'] == 1): ?>
                        <div class="project-card deleted-card">
                            <span class="admin-notice"><i class="fa-solid fa-triangle-exclamation"></i> Admin Deleted</span>
                            <h3 style="margin: 0; color: #7f1d1d;"><?php echo htmlspecialchars($p['project_name']); ?></h3>
                            <p style="font-size: 12px; color: #991b1b; margin-top: 10px;">This project was removed by the system administrator.</p>
                        </div>
                    <?php else: ?>
                        <div class="project-card" onclick="location.href='dashboard.php?project=<?php echo urlencode($p['project_name']); ?>'">
                            <i class="fa-solid fa-diagram-project" style="color: var(--bronze); font-size: 20px; margin-bottom: 15px;"></i>
                            <h3 style="margin: 0;"><?php echo htmlspecialchars($p['project_name']); ?></h3>
                            <p style="color: var(--slate); font-size: 12px; margin-top: 5px;">Last updated: <?php echo date('M d, Y', strtotime($p['updated_at'] ?? 'now')); ?></p>
                            <span class="btn-view">Open Architecture →</span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- NEW ARCHITECTURE MODAL -->
    <div id="nameModal">
        <div class="modal-content">
            <h2 style="margin: 0;">New Architecture</h2>
            <p style="color: var(--slate); font-size: 14px;">Give your new flow a name to get started.</p>
            <input type="text" id="projNameInput" class="modal-input" placeholder="e.g., E-commerce Architecture">
            <button onclick="startProject()" class="btn-create" style="width:100%; justify-content:center;">Initialize Flow</button>
            <p onclick="document.getElementById('nameModal').style.display='none'" style="margin-top:15px; cursor:pointer; color:var(--slate); font-size:13px; font-weight: 600;">Cancel</p>
        </div>
    </div>

    <!-- FEEDBACK FLOATING BUTTON -->
    <div class="feedback-trigger" onclick="toggleFeedback()">
        <i class="fa-solid fa-message"></i>
    </div>

    <!-- FEEDBACK PANEL -->
    <div id="feedbackPanel" class="feedback-panel">
        <div class="feedback-header">
            <span style="font-weight: 700;"><i class="fa-solid fa-comment-dots"></i> Feedback</span>
            <i class="fa-solid fa-xmark" onclick="toggleFeedback()" style="cursor:pointer;"></i>
        </div>
        <div class="feedback-body">
            <form action="submit_feedback.php" method="POST">
                <input type="hidden" name="user_name" value="<?php echo htmlspecialchars($user_name); ?>">
                <p style="margin: 0 0 10px; font-size: 13px; color: var(--slate); text-align: center;">Rate your experience</p>
                
                <div class="star-rating">
                    <input type="radio" id="star5" name="rating" value="5" required/><label for="star5" class="fa-solid fa-star"></label>
                    <input type="radio" id="star4" name="rating" value="4"/><label for="star4" class="fa-solid fa-star"></label>
                    <input type="radio" id="star3" name="rating" value="3"/><label for="star3" class="fa-solid fa-star"></label>
                    <input type="radio" id="star2" name="rating" value="2"/><label for="star2" class="fa-solid fa-star"></label>
                    <input type="radio" id="star1" name="rating" value="1"/><label for="star1" class="fa-solid fa-star"></label>
                </div>

                <textarea name="description" class="feedback-input" rows="4" placeholder="How can we improve DraftFlow?" required></textarea>
                <button type="submit" class="btn-create" style="width: 100%; justify-content: center;">Send Feedback</button>
            </form>
        </div>
    </div>

    <script>
        function startProject() {
            let name = document.getElementById('projNameInput').value;
            if(name.trim() === "") { alert("Please enter a name for your project."); return; }
            location.href = "dashboard.php?new=true&project=" + encodeURIComponent(name);
        }

        function toggleFeedback() {
            let panel = document.getElementById('feedbackPanel');
            panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
        }

        window.onclick = function(event) {
            let nameModal = document.getElementById('nameModal');
            let feedPanel = document.getElementById('feedbackPanel');
            if (event.target == nameModal) { nameModal.style.display = "none"; }
        }
    </script>
</body>
</html>