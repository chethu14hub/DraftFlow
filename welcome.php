<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DraftBoard | Professional System Architect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* 1. Added the high-end Serif font for your tagline sentence */
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;600&family=Cormorant+Garamond:ital,wght@1,400&display=swap');

        :root {
            --cream: #f5f5dc;
            --bronze: #8b795e;
            --dark: #2d2419;
            --glass: rgba(255, 255, 255, 0.03);
        }

        body, html {
            margin: 0; padding: 0; width: 100%; height: 100%;
            background-color: var(--dark);
            color: var(--cream);
            font-family: 'Outfit', sans-serif;
            overflow: hidden;
            display: flex; justify-content: center; align-items: center;
        }

        /* --- ARCHITECTURAL GRID --- */
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

        .content {
            position: relative; z-index: 10;
            text-align: center;
            padding: 60px;
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(139, 121, 94, 0.2);
            border-radius: 24px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.5);
            animation: containerSlide 1.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .logo-box {
            font-size: 80px;
            color: var(--bronze);
            margin-bottom: 30px;
            display: inline-block;
            filter: drop-shadow(0 0 15px rgba(139, 121, 94, 0.3));
        }

        h1 {
            font-size: 3.8rem;
            margin: 0;
            letter-spacing: 15px;
            font-weight: 600;
            text-transform: uppercase;
            background: linear-gradient(to bottom, #fff, #8b795e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0;
            animation: fadeIn 1s ease-out 0.5s forwards;
        }

        /* --- 2. UPDATED FONT FOR THE TAGLINE SENTENCE --- */
        .tagline {
            font-family: 'Cormorant Garamond', serif; /* New Professional Font */
            font-style: italic;
            font-weight: 400;
            font-size: 1.8rem; /* Large size for authority */
            margin: 15px 0 45px 0;
            opacity: 0;
            letter-spacing: 4px;
            color: rgba(245, 245, 220, 0.85);
            animation: focusIn 2s ease-out 1s forwards;
        }

        @keyframes focusIn {
            0% { filter: blur(10px); opacity: 0; letter-spacing: 12px; }
            100% { filter: blur(0); opacity: 1; letter-spacing: 4px; }
        }

        /* --- THE PRO BUTTON --- */
        .btn-enter {
            position: relative;
            padding: 18px 50px;
            background: transparent;
            color: var(--cream);
            border: 1px solid var(--bronze);
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 5px;
            font-weight: 400;
            transition: 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-block;
            opacity: 0;
            animation: fadeIn 1s ease-out 1.5s forwards;
            overflow: hidden;
            text-decoration: none;
        }

        .btn-enter:hover {
            letter-spacing: 8px;
            background: var(--bronze);
            color: var(--dark);
            box-shadow: 0 0 40px rgba(139, 121, 94, 0.6);
        }

        #status-text {
            font-size: 10px;
            letter-spacing: 2px;
            margin-top: 15px;
            text-transform: uppercase;
            color: var(--bronze);
            display: none;
        }

        @keyframes containerSlide {
            from { opacity: 0; transform: translateY(60px) scale(0.9); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="grid-bg"></div>

    <div class="content">
        <div class="logo-box">
            <i class="fa-solid fa-compass-drafting"></i>
        </div>
        <h1>DraftBoard</h1>
        
        <div class="tagline">Intelligent System Architecture Design</div>
        
        <div id="action-area">
            <button class="btn-enter" onclick="initializeSystem()">Enter Studio</button>
            <div id="status-text">Synchronizing Neural Modules...</div>
        </div>
    </div>

    <script>
        function initializeSystem() {
            const btn = document.querySelector('.btn-enter');
            const status = document.getElementById('status-text');
            
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Initializing';
            btn.style.letterSpacing = '2px';
            status.style.display = 'block';

            setTimeout(() => {
                window.location.href = "db_index.php"; 
            }, 1500);
        }
    </script>
</body>
</html>