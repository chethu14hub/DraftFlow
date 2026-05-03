<?php
session_start();
require_once('db_config.php');

// Security Check: Ensure only an admin can access this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { 
    header("Location: db_index.php"); 
    exit(); 
}

// Fetch feedback submissions - latest first
$query = "SELECT * FROM feedback ORDER BY submitted_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DraftFlow Admin | Feedback Review</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;600&family=Montserrat:wght@500&display=swap');

        :root {
            --cream: #fdfcf0;      /* Light Cream */
            --coffee: #6f5f4a;     /* Deep Coffee */
            --latte: #c2b280;      /* Soft Tan */
            --bronze: #8b795e;     /* Original Bronze Accent */
            --dark-text: #2d2419;  /* Deep Espresso for text */
        }

        body {
            background-color: var(--cream);
            color: var(--dark-text);
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 40px;
        }

        .admin-container {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid rgba(111, 95, 74, 0.2);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(111, 95, 74, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--cream);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 2px;
            color: var(--coffee);
            margin: 0;
            text-transform: uppercase;
            font-size: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            text-align: left;
            padding: 15px;
            color: var(--coffee);
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
            border-bottom: 2px solid var(--latte);
        }

        td {
            padding: 15px;
            border-bottom: 1px solid var(--cream);
            font-size: 14px;
            vertical-align: top;
            color: var(--dark-text);
        }

        .architect-name { 
            font-weight: 600; 
            color: var(--coffee); 
        }
        
        .rating-stars { 
            color: var(--bronze); 
            letter-spacing: 2px; 
            font-size: 16px;
        }
        
        .timestamp { 
            font-size: 12px; 
            color: var(--latte); 
        }

        .btn-back {
            text-decoration: none;
            color: var(--coffee);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 25px;
            padding: 10px 20px;
            border-radius: 8px;
            background: #ffffff;
            border: 1px solid var(--latte);
            transition: 0.3s;
            text-transform: uppercase;
            font-size: 12px;
        }

        .btn-back:hover {
            background: var(--coffee);
            color: var(--cream);
            transform: translateX(-5px);
        }

        .description-text {
            line-height: 1.6;
            color: #555;
            font-style: italic;
        }

        .empty-state {
            text-align: center;
            padding: 60px;
            color: var(--latte);
        }
    </style>
</head>
<body>

    <!-- Updated Navigation: Returns to Admin Oversight Home -->
    <a href="admin_panel.php" class="btn-back">
        <i class="fa-solid fa-house-chimney"></i> Back to Oversight Home
    </a>

    <div class="admin-container">
        <div class="header">
            <h1><i class="fa-solid fa-comments"></i> Architect Feedback</h1>
            <div style="font-size: 13px; font-weight: 600; color: var(--latte);">
                Total Submissions: <?php echo mysqli_num_rows($result); ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Architect</th>
                    <th>Rating</th>
                    <th>Experience Description</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="architect-name">
                                <i class="fa-solid fa-user-tie" style="font-size: 12px; opacity: 0.7;"></i> 
                                <?php echo htmlspecialchars($row['user_name']); ?>
                            </td>
                            <td>
                                <div class="rating-stars">
                                    <?php 
                                    for($i=1; $i<=5; $i++) {
                                        echo ($i <= $row['rating']) ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="description-text">
                                "<?php echo htmlspecialchars($row['description']); ?>"
                            </td>
                            <td class="timestamp">
                                <?php echo date('d M Y', strtotime($row['submitted_at'])); ?><br>
                                <?php echo date('h:i A', strtotime($row['submitted_at'])); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="empty-state">
                            <i class="fa-solid fa-feather-pointed fa-3x" style="margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                            The drafting table is quiet. No feedback yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>