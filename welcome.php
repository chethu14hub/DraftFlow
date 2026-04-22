<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DraftFlow | Professional System Architect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* 1. Import Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;600&family=Montserrat:wght@500&display=swap');

        :root {
            --cream: #f5f5dc;
            --bronze: #8b795e;
            --dark: #2d2419;
            --glass-white: rgba(255, 255, 255, 0.05); /* Pure frosted glass */
            --nav-bg: rgba(45, 36, 25, 0.85);
        }

        body, html {
            margin: 0; padding: 0; width: 100%; height: 100%;
            background-color: var(--dark);
            color: var(--cream);
            font-family: 'Outfit', sans-serif;
            overflow: hidden;
            display: flex; justify-content: center; align-items: center;
        }

        /* --- ARCHITECTURAL GRID BACKGROUND --- */
        .grid-bg {
            position: absolute; width: 200%; height: 200%;
            background-image: 
                linear-gradient(rgba(139, 121, 94, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(139, 121, 94, 0.1) 1px, transparent 1px);
            background-size: 60px 60px;
            transform: perspective(500px) rotateX(60deg);
            bottom: -50%;
            animation: gridMove 20s linear infinite;
        }

        @keyframes gridMove {
            from { transform: perspective(500px) rotateX(60deg) translateY(0); }
            to { transform: perspective(500px) rotateX(60deg) translateY(60px); }
        }

        /* --- TOP NAVIGATION --- */
        .top-nav {
            position: fixed;
            top: 0; left: 0; width: 100%;
            padding: 25px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            box-sizing: border-box;
            background: var(--nav-bg);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(139, 121, 94, 0.1);
        }

        .nav-logo {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 20px;
            letter-spacing: 4px;
            color: var(--bronze);
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-links a {
            text-decoration: none;
            color: rgba(245, 245, 220, 0.6);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .nav-links a:hover {
            color: var(--cream);
            text-shadow: 0 0 10px rgba(139, 121, 94, 0.5);
        }

        .nav-divider {
            width: 1px;
            height: 15px;
            background: rgba(139, 121, 94, 0.3);
            margin: 0 10px;
        }

        .btn-login {
            border: 1px solid rgba(139, 121, 94, 0.4);
            padding: 10px 25px;
            border-radius: 4px;
        }

        .nav-signin {
            background: var(--bronze);
            color: var(--dark) !important;
            padding: 10px 25px;
            border-radius: 4px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(139, 121, 94, 0.2);
        }

        /* --- UPDATED GLASS CONTENT BOX --- */
        .content {
            position: relative; z-index: 10;
            text-align: center;
            padding: 80px 60px;
            max-width: 800px;
            
            /* Pure Glass: Transparent white, NO BLUE */
            background: var(--glass-white); 
            
            /* High-End Frosted Effect */
            backdrop-filter: blur(30px) saturate(140%);
            -webkit-backdrop-filter: blur(30px) saturate(140%);
            
            border: 1px solid rgba(139, 121, 94, 0.2);
            border-radius: 35px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.6);
            
            animation: containerSlide 1.2s cubic-bezier(0.16, 1, 0.3, 1);
            transition: transform 0.4s ease;
        }

        .content:hover {
            transform: translateY(-5px);
        }

        .logo-box {
            font-size: 90px;
            color: var(--bronze);
            margin-bottom: 20px;
            display: inline-block;
            filter: drop-shadow(0 0 20px rgba(139, 121, 94, 0.3));
        }

        h1 {
            font-size: 4rem;
            margin: 0;
            letter-spacing: 18px;
            font-weight: 600;
            text-transform: uppercase;
            background: linear-gradient(to bottom, #fff, #8b795e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0;
            animation: fadeIn 1s ease-out 0.5s forwards;
        }

        .tagline {
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            font-size: 1rem;
            margin-top: 30px;
            opacity: 0;
            letter-spacing: 10px;
            text-transform: uppercase;
            color: rgba(245, 245, 220, 0.5); 
            animation: slideFade 1.5s ease-out 0.8s forwards;
        }

        /* --- ABOUT MODAL --- */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(15px);
            display: none;
            justify-content: center; align-items: center;
            z-index: 2000;
        }

        .modal-content {
            background: var(--dark);
            padding: 50px;
            border-radius: 24px;
            border: 1px solid var(--bronze);
            max-width: 550px;
            text-align: center;
            box-shadow: 0 50px 100px rgba(0,0,0,0.8);
        }

        .modal-content h2 {
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: var(--bronze);
            margin-bottom: 25px;
        }

        .modal-content p {
            font-size: 14px;
            line-height: 1.8;
            color: rgba(245, 245, 220, 0.8);
            margin-bottom: 35px;
        }

        .btn-close {
            padding: 12px 35px;
            background: transparent;
            border: 1px solid var(--bronze);
            color: var(--cream);
            text-transform: uppercase;
            letter-spacing: 3px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-close:hover {
            background: var(--bronze);
            color: var(--dark);
        }

        /* --- ANIMATIONS --- */
        @keyframes containerSlide {
            from { opacity: 0; transform: translateY(40px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideFade {
            from { opacity: 0; letter-spacing: 15px; transform: translateY(10px); }
            to { opacity: 1; letter-spacing: 10px; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="grid-bg"></div>

    <nav class="top-nav">
        <div class="nav-logo">
            <i class="fa-solid fa-compass-drafting"></i> DraftFlow
        </div>
        <div class="nav-links">
            <a onclick="toggleAbout(true)">About Us</a>
            <div class="nav-divider"></div>
            <a href="db_index.php" class="btn-login">Login</a>
            <a href="db_index.php#register" class="nav-signin">Sign In</a>
        </div>
    </nav>

    <div class="content">
        <div class="logo-box">
            <i class="fa-solid fa-compass-drafting"></i>
        </div>
        <h1>DraftFlow</h1>
        <div class="tagline">Intelligent System Architecture Design</div>
    </div>

    <div id="about-modal" class="modal-overlay" onclick="toggleAbout(false)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <h2>Our Mission</h2>
            <p>DraftFlow is a specialized architectural canvas that simplifies the transition from abstract logic to infrastructure planning by providing an intuitive, drag-and-drop workspace. It serves as an intelligent blueprinting tool where developers can visualize complex software stacks, connect modular components like frontends and databases, and utilize AI-assisted validation to ensure structural integrity.</p>
            <button class="btn-close" onclick="toggleAbout(false)">Close Blueprint</button>
        </div>
    </div>

    <script>
        function toggleAbout(show) {
            const modal = document.getElementById('about-modal');
            modal.style.display = show ? 'flex' : 'none';
        }
    </script>
</body>
</html>