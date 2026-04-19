<?php
session_start();
require_once('db_config.php');
if (!isset($_SESSION['user_id'])) { header("Location: db_index.php"); exit(); }

$user_name = $_SESSION['user_name'] ?? "Architect";
$user_id = $_SESSION['user_id'];
$project_name = $_GET['project'] ?? "New System Architecture";

<<<<<<< HEAD
// --- LOAD DATA FROM DATABASE ---
=======
// --- GROQ AI INTERNAL BRIDGE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'chat') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $apiKey = $_ENV['GROQ_API_KEY'];

    $systemInstructions = "You are the DraftFlow Pro AI Guide for $user_name. 
    1. Drag modules from the left to create nodes.
    2. Click nodes to configure tech stacks.
    3. Drag bronze dots to connect.
    Provide concise software architecture advice.";

    $data = [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => [['role' => 'system', 'content' => $systemInstructions], ['role' => 'user', 'content' => $input['message'] ?? '']],
        'temperature' => 0.7
    ];

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);
    echo curl_exec($ch);
    exit();
}

>>>>>>> c631a520ebf19dbf936074dbab1d7ccc72e9f24f
$saved_data = "{}"; 
$stmt = $conn->prepare("SELECT project_data FROM projects WHERE user_id = ? AND project_name = ?");
$stmt->bind_param("is", $user_id, $project_name);
$stmt->execute();
$result = $stmt->get_result();
<<<<<<< HEAD
if ($row = $result->fetch_assoc()) {
    $saved_data = $row['project_data'];
}
?>
<!DOCTYPE html>
<html lang="en">s
=======
if ($row = $result->fetch_assoc()) { $saved_data = $row['project_data']; }
?>
<!DOCTYPE html>
<html lang="en">
>>>>>>> c631a520ebf19dbf936074dbab1d7ccc72e9f24f
<head>
    <meta charset="UTF-8">
    <title>DraftFlow Pro | <?php echo htmlspecialchars($project_name); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsPlumb/2.15.6/js/jsplumb.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <style>
<<<<<<< HEAD
        :root { --cream: #f5f5dc; --bronze: #8b795e; --dark: #0f172a; --accent: #3b82f6; }
        body { margin: 0; display: flex; height: 100vh; font-family: 'Inter', sans-serif; background: var(--cream); overflow: hidden; }

        /* SIDEBAR */
        .sidebar { width: 260px; background: #0f172a; color: white; padding: 20px; z-index: 1003; display: flex; flex-direction: column; box-shadow: 4px 0 10px rgba(0,0,0,0.2); }
        .sidebar h2 { font-size: 12px; letter-spacing: 2px; color: var(--bronze); text-transform: uppercase; border-bottom: 1px solid #334155; padding-bottom: 10px; margin-bottom: 20px; }
        .tool-group-label { font-size: 10px; color: #64748b; font-weight: 800; margin: 15px 0 8px; text-transform: uppercase; }
        .tool-item { padding: 10px 14px; background: #1e293b; border-radius: 8px; margin-bottom: 8px; cursor: grab; display: flex; align-items: center; gap: 12px; font-size: 13px; transition: 0.2s; border: 1px solid transparent; }
        .tool-item:hover { background: #334155; border-color: var(--bronze); }
        .tool-item i { width: 20px; text-align: center; color: var(--bronze); }

        /* CANVAS AREA */
        .main-stage { flex: 1; position: relative; display: flex; flex-direction: column; padding-top: 65px; }
        .project-header { position: fixed; top: 0; left: 260px; right: 0; height: 65px; background: white; border-bottom: 1px solid #cbd5e1; display: flex; align-items: center; padding: 0 30px; z-index: 1002; }
        
        /* TOP LEFT STATUS ON CANVAS */
        .canvas-status { 
            position: absolute; top: 20px; left: 20px; z-index: 1000; 
            background: rgba(255,255,255,0.9); padding: 12px 20px; 
            border-radius: 10px; border: 1px solid #cbd5e1; 
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            pointer-events: none;
        }
        .canvas-status b { color: var(--dark); display: block; font-size: 15px; margin-bottom: 2px; }
        .canvas-status span { color: var(--bronze); font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }

        #canvas { flex: 1; position: relative; background-image: radial-gradient(#d1d1b3 1px, transparent 1px); background-size: 30px 30px; overflow: hidden; }

        /* NODES */
        .node { position: absolute; width: 140px; min-height: 100px; padding: 12px; background: white; border: 1px solid var(--bronze); border-radius: 12px; text-align: center; z-index: 100; box-shadow: 0 10px 15px rgba(0,0,0,0.05); cursor: move; }
        .node.selected { border: 2px solid var(--accent); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        .node i { font-size: 32px; color: #334155; display: block; margin-bottom: 5px; height: 32px; line-height: 32px; }
        .type-label { display: block; font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--bronze); margin-bottom: 8px; }
        .node-stack-display { display: flex; flex-wrap: wrap; justify-content: center; gap: 3px; }
        .tech-pill { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: 700; color: #475569; border: 1px solid #e2e8f0; }
        .dot { width: 12px; height: 12px; background: var(--bronze); border-radius: 50%; position: absolute; right: -6px; top: 45px; cursor: crosshair; z-index: 110; border: 2px solid white; }

        /* PROPERTY PANEL */
        .details-panel { width: 380px; background: white; border-left: 1px solid #e2e8f0; display: none; padding: 25px; z-index: 1001; overflow-y: auto; }
        .section-label { font-size: 11px; font-weight: 800; color: var(--bronze); margin: 20px 0 8px; display: block; text-transform: uppercase; }
        select, input[type="text"] { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 10px; }
        .checkbox-group { background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #f1f5f9; }
        .checkbox-item { display: flex; align-items: center; gap: 10px; font-size: 13px; margin-bottom: 8px; cursor: pointer; }
        
        .btn-tool { padding: 10px 18px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; cursor: pointer; font-size: 12px; display: flex; align-items: center; gap: 8px; font-weight: 600; }
=======
        :root { --cream: #f5f5dc; --bronze: #8b795e; --dark: #0f172a; --accent: #3b82f6; --slate: #64748b; }
        body { margin: 0; display: flex; height: 100vh; font-family: 'Inter', sans-serif; background: var(--cream); overflow: hidden; }

        .sidebar { width: 300px; background: #0f172a; color: white; padding: 15px; z-index: 1003; display: flex; flex-direction: column; box-shadow: 4px 0 10px rgba(0,0,0,0.2); height: 100vh; }
        .sidebar h2 { font-size: 14px; color: var(--bronze); text-transform: uppercase; letter-spacing: 2px; border-bottom: 1px solid #334155; padding-bottom: 10px; margin-bottom: 15px; }
        .modules-container { flex: 0 0 auto; max-height: 45%; overflow-y: auto; margin-bottom: 15px; padding-right: 5px; }
        .tool-item { padding: 12px; background: #1e293b; border-radius: 8px; margin-bottom: 8px; cursor: grab; display: flex; align-items: center; gap: 12px; font-size: 13px; transition: 0.3s; border: 1px solid transparent; }
        .tool-item:hover { background: #334155; border-color: var(--bronze); transform: translateX(5px); }
        .tool-item i { color: var(--bronze); width: 20px; text-align: center; font-size: 16px; }

        #ai-sidebar-chat { flex: 1; display: flex; flex-direction: column; background: #1e293b; border-radius: 10px; border: 1px solid #334155; overflow: hidden; margin-top: 5px; margin-bottom: 15px; }
        #chat-header { background: var(--bronze); color: #0f172a; padding: 10px; font-weight: 800; font-size: 11px; text-transform: uppercase; display: flex; align-items: center; gap: 8px; }
        #chat-body { flex: 1; overflow-y: auto; padding: 12px; font-size: 12px; display: flex; flex-direction: column; gap: 8px; scrollbar-width: thin; background: #16202e; }
        #chat-input-area { padding: 12px 10px; background: #0f172a; display: flex; gap: 5px; border-top: 1px solid #334155; }
        #chat-input { flex: 1; background: #1e293b; border: 1px solid #334155; color: white; padding: 8px; border-radius: 6px; font-size: 12px; outline: none; }
        .message { padding: 8px 10px; border-radius: 8px; max-width: 90%; line-height: 1.4; word-wrap: break-word; }
        .bot-msg { background: #334155; color: #e2e8f0; align-self: flex-start; border-bottom-left-radius: 2px; }
        .user-msg { background: var(--accent); color: white; align-self: flex-end; border-bottom-right-radius: 2px; }

        .main-stage { flex: 1; position: relative; overflow: hidden; display: flex; flex-direction: column; }
        .project-header { height: 65px; background: white; border-bottom: 1px solid #cbd5e1; display: flex; align-items: center; padding: 0 30px; z-index: 1002; }
        #canvas { flex: 1; position: relative; background-image: radial-gradient(#d1d1b3 1px, transparent 1px); background-size: 30px 30px; cursor: crosshair; width: 5000px; height: 5000px; transform-origin: 0 0; transition: transform 0.1s ease-out; }
        #canvas.panning { cursor: grab !important; }

        /* PROFESSIONAL ARCHITECT TAG */
        .architect-badge { position: absolute; bottom: 25px; left: 25px; z-index: 1000; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(5px); color: white; padding: 10px 20px; border-radius: 8px; display: flex; align-items: center; gap: 12px; border-left: 4px solid var(--bronze); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .architect-badge i { color: var(--bronze); font-size: 18px; }
        .architect-badge .info { display: flex; flex-direction: column; }
        .architect-badge .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: var(--slate); font-weight: 800; }
        .architect-badge .name { font-size: 13px; font-weight: 700; color: var(--cream); }

        .node { position: absolute; width: 170px; min-height: 110px; height: auto; padding: 12px; background: white; border: 1px solid var(--bronze); border-radius: 12px; text-align: center; z-index: 100; box-shadow: 0 10px 15px rgba(0,0,0,0.05); cursor: move; display: flex; flex-direction: column; }
        .node.selected { border: 2.5px solid var(--accent); transform: scale(1.02); }
        .node i { font-size: 28px; color: #334155; display: block; margin-bottom: 8px; pointer-events: none; flex-shrink: 0; }
        .node-stack-display { display: flex; flex-direction: column; gap: 4px; margin-top: 5px; flex-grow: 1; }
        .tech-pill { background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-size: 9px; font-weight: 700; color: #475569; border: 1px solid #e2e8f0; text-align: left; }
        .dot { width: 12px; height: 12px; background: var(--bronze); border-radius: 50%; position: absolute; right: -6px; top: 45px; cursor: crosshair; z-index: 110; border: 2px solid white; }

        .details-panel { width: 380px; background: #ffffff; border-left: 1px solid #e2e8f0; display: none; padding: 25px; z-index: 1001; overflow-y: auto; box-shadow: -5px 0 20px rgba(0,0,0,0.05); }
        .group-container { background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid #f1f5f9; margin-bottom: 12px; }
        .section-label { font-size: 10px; font-weight: 800; color: var(--bronze); margin-bottom: 8px; display: block; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        .config-select { width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; margin-bottom: 8px; font-size: 12px; background: white; }
        .checkbox-item { display: flex; align-items: center; gap: 8px; font-size: 12px; margin-bottom: 5px; cursor: pointer; }
        .btn-tool { padding: 10px 20px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; cursor: pointer; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
>>>>>>> c631a520ebf19dbf936074dbab1d7ccc72e9f24f
        .btn-save { background: #0f172a; color: white; border: none; }
    </style>
</head>
<body>

    <div class="sidebar">
<<<<<<< HEAD
        <h2>DraftFlow Components</h2>
        <div class="tool-group-label">Logic & UI</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="frontend"><i class="fa-solid fa-desktop"></i> Frontend</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="backend"><i class="fa-solid fa-server"></i> Backend</div>
        <div class="tool-group-label">Data</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="database"><i class="fa-solid fa-database"></i> Database</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="cache"><i class="fa-solid fa-bolt"></i> Redis/Cache</div>
        <div class="tool-group-label">Cloud</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="aws"><i class="fa-brands fa-aws"></i> AWS Cloud</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="gcp"><i class="fa-brands fa-google"></i> Google Cloud</div>
=======
        <h2>Modules</h2>
        <div class="modules-container">
            <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="frontend"><i class="fa-solid fa-desktop"></i> Frontend</div>
            <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="backend"><i class="fa-solid fa-server"></i> Backend</div>
            <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="database"><i class="fa-solid fa-database"></i> Database</div>
            <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="cloud"><i class="fa-solid fa-cloud"></i> Cloud / DevOps</div>
            <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="security"><i class="fa-solid fa-shield-halved"></i> Security</div>
            <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="ai"><i class="fa-solid fa-robot"></i> AI Model</div>
            <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="integration"><i class="fa-solid fa-link"></i> Integrations</div>
        </div>

        <div id="ai-sidebar-chat">
            <div id="chat-header"><i class="fa-solid fa-brain"></i> AI Architect</div>
            <div id="chat-body"><div class="message bot-msg">Hello! I'm your architectural consultant. How can I help with your design?</div></div>
            <div id="chat-input-area">
                <input type="text" id="chat-input" placeholder="Ask a question...">
                <button onclick="sendMessage()" style="background:var(--bronze); border:none; padding:5px 10px; border-radius:4px; cursor:pointer;"><i class="fa-solid fa-paper-plane" style="color:#0f172a;"></i></button>
            </div>
        </div>
>>>>>>> c631a520ebf19dbf936074dbab1d7ccc72e9f24f
    </div>

    <div class="main-stage">
        <div class="project-header">
<<<<<<< HEAD
            <div style="margin-left: auto; display: flex; gap: 12px;">
                <button onclick="undo()" class="btn-tool"><i class="fa-solid fa-rotate-left"></i> Undo</button>
                <button onclick="redo()" class="btn-tool"><i class="fa-solid fa-rotate-right"></i> Redo</button>
                <button onclick="exportJPG()" class="btn-tool"><i class="fa-solid fa-file-image"></i> Export JPG</button>
                <button onclick="saveProjectData()" class="btn-tool btn-save"><i class="fa-solid fa-floppy-disk"></i> Save Project</button>
                <button onclick="location.href='portal.php'" class="btn-tool"><i class="fa-solid fa-house"></i> Dashboard</button>
            </div>
        </div>

        <div class="canvas-status">
            <b><i class="fa-solid fa-folder-open" style="color:var(--bronze); margin-right:8px;"></i><?php echo htmlspecialchars($project_name); ?></b>
            <span><i class="fa-solid fa-user-tie" style="margin-right:5px;"></i> Architect: <?php echo htmlspecialchars($user_name); ?></span>
        </div>

        <div id="canvas" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
    </div>

    <div class="details-panel" id="details-panel">
        <h3 id="panel-title">Configuration</h3>
        <hr>
        <div id="dynamic-fields"></div>
        <div style="margin-top: 30px; display: flex; flex-direction: column; gap: 10px;">
            <button onclick="closePanel()" class="btn-tool" style="width:100%; justify-content:center; background:#f1f5f9;">Save & Close</button>
            <button onclick="deleteSelected()" class="btn-tool" style="width:100%; justify-content:center; color:#ef4444;">Delete Component</button>
=======
            <div style="font-size: 14px; font-weight: 800; color: var(--dark);"><i class="fa-solid fa-folder-open" style="color:var(--bronze);"></i> <?php echo htmlspecialchars($project_name); ?></div>
            <div style="margin-left: auto; display: flex; gap: 12px;">
                <button onclick="undo()" class="btn-tool">Undo</button>
                <button onclick="redo()" class="btn-tool">Redo</button>
                <button onclick="exportJPG()" class="btn-tool">Export JPG</button>
                <button onclick="saveProjectData()" class="btn-tool btn-save">Save Project</button>
                <button onclick="location.href='portal.php'" class="btn-tool">Exit</button>
            </div>
        </div>
        
        <div class="architect-badge">
            <i class="fa-solid fa-user-gear"></i>
            <div class="info">
                <span class="label">Project Architect</span>
                <span class="name"><?php echo htmlspecialchars($user_name); ?></span>
            </div>
        </div>

        <div id="canvas" ondrop="drop(event)" ondragover="allowDrop(event)" onclick="closePanel()"></div>
    </div>

    <div class="details-panel" id="details-panel" onclick="event.stopPropagation()">
        <h3 id="panel-title">Configuration</h3>
        <div id="dynamic-fields"></div>
        <div style="margin-top: 30px; display: flex; flex-direction: column; gap: 10px;">
            <button onclick="closePanel()" class="btn-tool btn-save" style="justify-content:center;">Apply & Sync</button>
            <button onclick="deleteSelected()" class="btn-tool" style="justify-content:center; color:#ef4444; border: 1.5px solid #fecaca;"><i class="fa-solid fa-trash-can"></i> Delete Node</button>
>>>>>>> c631a520ebf19dbf936074dbab1d7ccc72e9f24f
        </div>
    </div>

    <script>
        let nodeDataStore = {};
        let selectedNode = null;
<<<<<<< HEAD
        let undoStack = [];
        let redoStack = [];

        const savedProjectData = <?php echo $saved_data; ?>;

        let instance = jsPlumb.getInstance({
            Connector: ["Flowchart", { stub: 40, cornerRadius: 5 }],
            PaintStyle: { stroke: "#8b795e", strokeWidth: 2.5 },
            Endpoint: ["Dot", { radius: 5 }],
=======
        let undoStack = [], redoStack = [];
        let isSpaceDown = false;
        let isDraggingCanvas = false;
        let startX, startY, currentX = 0, currentY = 0;
        let zoomLevel = 1;

        const savedProjectData = <?php echo $saved_data; ?>;
        const canvas = document.getElementById('canvas');

        let instance = jsPlumb.getInstance({
            Connector: ["Flowchart", { stub: 30, cornerRadius: 5 }],
            PaintStyle: { stroke: "#8b795e", strokeWidth: 2.5 },
            Endpoint: ["Dot", { radius: 4 }],
>>>>>>> c631a520ebf19dbf936074dbab1d7ccc72e9f24f
            EndpointStyle: { fill: "#8b795e" },
            Container: "canvas"
        });

<<<<<<< HEAD
        // --- UNDO / REDO LOGIC ---
        function saveState() {
            const state = JSON.stringify({
                nodes: JSON.parse(JSON.stringify(nodeDataStore)),
                positions: Array.from(document.querySelectorAll('.node')).map(n => ({ id: n.id, left: n.style.left, top: n.style.top })),
                wires: instance.getAllConnections().map(c => ({ s: c.sourceId, t: c.targetId }))
            });
            undoStack.push(state);
            if(undoStack.length > 30) undoStack.shift();
            redoStack = []; 
        }

        document.addEventListener('keydown', e => {
            if (e.ctrlKey && e.key === 'z') { e.preventDefault(); undo(); }
            if (e.ctrlKey && e.key === 'y') { e.preventDefault(); redo(); }
        });

        function undo() {
            if (undoStack.length < 2) return;
            redoStack.push(undoStack.pop());
            applyState(JSON.parse(undoStack[undoStack.length - 1]));
        }

        function redo() {
            if (redoStack.length === 0) return;
            const state = redoStack.pop();
            undoStack.push(state);
            applyState(JSON.parse(state));
        }

        function applyState(state) {
            instance.deleteEveryConnection();
            document.querySelectorAll('.node').forEach(n => instance.remove(n));
            nodeDataStore = state.nodes;
            state.positions.forEach(pos => {
                const data = state.nodes[pos.id];
                // Pass the node type to getIcon to ensure the icon is recovered correctly
                createNode(data.type, getIcon(data.type), pos.left, pos.top, pos.id, false);
                updateNodeCardDisplay(pos.id);
            });
            setTimeout(() => {
                state.wires.forEach(w => instance.connect({ source: w.s, target: w.t }));
            }, 100);
        }

        // --- ICON RESOLVER FIX ---
        function getIcon(type) {
            const icons = { 
                frontend: 'fa-solid fa-desktop', 
                backend: 'fa-solid fa-server', 
                database: 'fa-solid fa-database', 
                cache: 'fa-solid fa-bolt', 
                aws: 'fa-brands fa-aws', 
                gcp: 'fa-brands fa-google' 
            };
            return icons[type] || 'fa-solid fa-cube';
        }

        window.onload = function() {
            if (savedProjectData && savedProjectData.nodes) applyState(savedProjectData);
            else saveState();
        };

        function allowDrop(ev) { ev.preventDefault(); }
        function drag(ev) { 
            ev.dataTransfer.setData("type", ev.target.dataset.type); 
            ev.dataTransfer.setData("icon", ev.target.querySelector('i').className); 
        }
        
        function drop(ev) {
            ev.preventDefault();
            const rect = document.getElementById('canvas').getBoundingClientRect();
            createNode(ev.dataTransfer.getData("type"), ev.dataTransfer.getData("icon"), (ev.clientX - rect.left - 70) + 'px', (ev.clientY - rect.top - 50) + 'px');
            saveState();
        }

        function createNode(type, icon, left, top, id = "n" + Date.now(), shouldSave = true) {
            const div = document.createElement('div');
            div.className = 'node'; div.id = id; div.dataset.type = type;
            div.style.left = left; div.style.top = top;
            
            // Clean up icon class string to prevent double fa-solid prefixes
            let iconClass = icon;
            if(!icon.includes('fa-')) iconClass = 'fa-solid ' + icon;

            div.innerHTML = `<span class="type-label">${type}</span><i class="${iconClass}"></i><div class="node-stack-display" id="stack-${id}"></div><div class="dot"></div>`;
            
            div.onclick = (e) => {
                if(selectedNode) selectedNode.classList.remove('selected');
                selectedNode = div; div.classList.add('selected');
                openDetails(id, type);
                e.stopPropagation();
            };

            document.getElementById('canvas').appendChild(div);
            instance.draggable(div, { stop: saveState });
            instance.makeSource(div, { filter: ".dot", anchor: "Right" });
            instance.makeTarget(div, { anchor: "Left" });

            if(!nodeDataStore[id]) nodeDataStore[id] = { type, langs: [], fw: "" };
            if(shouldSave) saveState();
        }

        // --- LANGUAGE & FRAMEWORK SELECTION (Restored & Kept) ---
        function openDetails(id, type) {
            const panel = document.getElementById('details-panel');
            const fields = document.getElementById('dynamic-fields');
            panel.style.display = 'block';
            const data = nodeDataStore[id];
            let html = "";

            if(['frontend', 'backend'].includes(type)) {
                const options = type === 'frontend' ? ["React", "Vue", "Next.js", "Angular"] : ["Laravel", "Node.js", "Django", "Spring"];
                const langs = type === 'frontend' ? ["HTML", "CSS", "JS", "TS"] : ["PHP", "JS", "Python", "Java"];
                
                html += `<span class="section-label">Framework</span>`;
                html += `<select onchange="updateNodeData('${id}','fw',this.value)"><option value="">None</option>`;
                options.forEach(o => html += `<option value="${o}" ${data.fw===o?'selected':''}>${o}</option>`);
                html += `</select>`;

                html += `<span class="section-label">Languages</span><div class="checkbox-group">`;
                langs.forEach(l => html += `
                    <label class="checkbox-item">
                        <input type="checkbox" ${data.langs.includes(l)?'checked':''} onchange="updateCheck('${id}','${l}',this.checked)"> ${l}
                    </label>`);
                html += `</div>`;
            } else {
                html += `<span class="section-label">Config / Name</span>`;
                html += `<input type="text" value="${data.fw || ''}" onchange="updateNodeData('${id}','fw',this.value)">`;
=======
        window.addEventListener('keydown', e => {
            if(e.code === 'Space' && document.activeElement.id !== 'chat-input') { 
                isSpaceDown = true; canvas.classList.add('panning'); e.preventDefault(); 
            }
            if (e.ctrlKey && e.key === 'z' && !e.shiftKey) { e.preventDefault(); undo(); }
            if (e.key === 'Enter' && document.activeElement.id === 'chat-input') { sendMessage(); }
        });
        window.addEventListener('keyup', e => { if(e.code === 'Space') { isSpaceDown = false; canvas.classList.remove('panning'); isDraggingCanvas = false; } });

        window.addEventListener('wheel', e => {
            if (e.ctrlKey) {
                e.preventDefault();
                const zoomSpeed = 0.05;
                if (e.deltaY < 0) zoomLevel = Math.min(zoomLevel + zoomSpeed, 2);
                else zoomLevel = Math.max(zoomLevel - zoomSpeed, 0.5);
                canvas.style.transform = `translate(${currentX}px, ${currentY}px) scale(${zoomLevel})`;
                instance.setZoom(zoomLevel);
            }
        }, { passive: false });

        document.addEventListener('mousedown', e => { if(isSpaceDown && e.target === canvas) { isDraggingCanvas = true; startX = e.clientX - currentX; startY = e.clientY - currentY; } });
        document.addEventListener('mousemove', e => { if(isDraggingCanvas && isSpaceDown) { currentX = e.clientX - startX; currentY = e.clientY - startY; canvas.style.transform = `translate(${currentX}px, ${currentY}px) scale(${zoomLevel})`; } });
        document.addEventListener('mouseup', () => { isDraggingCanvas = false; });

        async function sendMessage() {
            const input = document.getElementById('chat-input');
            const chatBody = document.getElementById('chat-body');
            const userMsg = input.value.trim();
            if(!userMsg) return;
            const uDiv = document.createElement('div'); uDiv.className = 'message user-msg'; uDiv.textContent = userMsg; chatBody.appendChild(uDiv);
            input.value = ''; chatBody.scrollTop = chatBody.scrollHeight;
            const typingDiv = document.createElement('div'); typingDiv.className = 'message bot-msg'; typingDiv.textContent = "..."; chatBody.appendChild(typingDiv);
            try {
                const response = await fetch('dashboard.php?action=chat', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ message: userMsg }) });
                const data = await response.json();
                typingDiv.innerHTML = data.choices[0].message.content.replace(/\n/g, '<br>');
            } catch (error) { typingDiv.textContent = "AI unreachable."; }
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        instance.bind("click", c => { if(confirm("Remove connection?")) { instance.deleteConnection(c); saveState(); } });

        function saveState() {
            const state = JSON.stringify({ nodes: JSON.parse(JSON.stringify(nodeDataStore)), positions: Array.from(document.querySelectorAll('.node')).map(n => ({ id: n.id, left: n.style.left, top: n.style.top })), wires: instance.getAllConnections().map(c => ({ s: c.sourceId, t: c.targetId })) });
            undoStack.push(state); if(undoStack.length > 50) undoStack.shift();
        }

        function createNode(type, icon, left, top, id = "n" + Date.now(), shouldSave = true) {
            const div = document.createElement('div'); div.className = 'node'; div.id = id; div.dataset.type = type; div.style.left = left; div.style.top = top;
            div.innerHTML = `<span class="type-label" style="font-size:9px; color:#8b795e; display:block; margin-bottom:4px;">${type}</span><i class="${icon}"></i><div class="node-stack-display" id="stack-${id}"></div><div class="dot"></div>`;
            div.onclick = (e) => { if(selectedNode) selectedNode.classList.remove('selected'); selectedNode = div; div.classList.add('selected'); openDetails(id, type); e.stopPropagation(); };
            canvas.appendChild(div); instance.draggable(div, { stop: saveState }); instance.makeSource(div, { filter: ".dot", anchor: "Right" }); instance.makeTarget(div, { anchor: "Left" });
            if(!nodeDataStore[id]) nodeDataStore[id] = { type, langs: [], fw: [], styling: [], advanced: [] };
            if(shouldSave) saveState();
        }

        function openDetails(id, type) {
            const panel = document.getElementById('details-panel'); const fields = document.getElementById('dynamic-fields'); panel.style.display = 'block';
            const data = nodeDataStore[id]; let html = "";
            if(type === 'frontend') {
                html += renderGroup("🟢 Languages", ["HTML", "CSS", "JavaScript", "TypeScript"], "langs", id);
                html += renderSelect("🟢 Frameworks", ["React", "Angular", "Vue", "Svelte", "Next.js", "Nuxt.js"], "fw", id, data.fw[0]);
                html += renderGroup("🟢 Styling", ["Tailwind CSS", "Bootstrap", "Material UIS", "SCSS / Sass"], "styling", id);
                html += renderGroup("🟢 State Management", ["Redux", "Zustand", "Context API", "MobX"], "advanced", id);
            } else if(type === 'backend') {
                html += renderSelect("🔵 Languages", ["Node.js", "Python", "Java", "PHP", "Go"], "langs", id, data.langs[0]);
                html += renderSelect("🔵 Frameworks", ["Express.js", "Django", "Flask", "Laravel", "Spring Boot"], "fw", id, data.fw[0]);
                html += renderSelect("🔵 API Type", ["REST", "GraphQL", "gRPC"], "styling", id, data.styling[0]);
            } else if(type === 'database') {
                html += renderGroup("🟣 SQL", ["MySQL", "PostgreSQL", "SQLite"], "langs", id);
                html += renderGroup("🟣 NoSQL", ["MongoDB", "Redis", "Cassandra"], "fw", id);
                html += renderSelect("🟣 Caching", ["Redis", "Memcached"], "styling", id, data.styling[0]);
            } else if(type === 'cloud') {
                 html += renderSelect("🟠 Providers", ["AWS", "Azure", "Google Cloud"], "langs", id, data.langs[0]);
                html += renderGroup("🟠 Hosting", ["Vercel", "Netlify", "DigitalOcean"], "fw", id);
                html += renderGroup("🟠 Containers", ["Docker", "Kubernetes", "Jenkins"], "styling", id);
                html += renderGroup("🟠 Storage", ["AWS S3", "Firebase"], "advanced", id);

            } else if(type === 'ai') {
                html += renderSelect("🤖 Provider", ["Groq", "OpenAI", "Anthropic", "Gemini"], "langs", id, data.langs[0]);
                html += renderSelect("🤖 Model", ["llama-3.3", "gpt-4o", "claude-3.5", "gemini-1.5"], "fw", id, data.fw[0]);
                html += renderSelect("🤖 Task", ["Chat", "Embedding", "Image Gen"], "styling", id, data.styling[0]);
            } else if(type === 'security') {
                html += renderGroup("🔒 Auth", ["OAuth 2.0", "JWT", "Firebase Auth", "Auth0"], "langs", id);
                html += renderGroup("🔒 Protection", ["HTTPS / SSL", "CORS", "Rate Limiting"], "fw", id);
                html += renderGroup("🔒 Monitoring", ["Sentry", "CloudWatch", "Datadog"], "styling", id);
            } else if(type === 'integration') {
                html += renderSelect("🔗 Tool", ["Stripe", "Auth0", "Twilio", "SendGrid"], "langs", id, data.langs[0]);
>>>>>>> c631a520ebf19dbf936074dbab1d7ccc72e9f24f
            }
            fields.innerHTML = html;
        }

<<<<<<< HEAD
        function updateNodeData(id, k, v) {
            nodeDataStore[id][k] = v;
            updateNodeCardDisplay(id);
            saveState();
        }

        function updateCheck(id, lang, checked) {
            if(checked) nodeDataStore[id].langs.push(lang);
            else nodeDataStore[id].langs = nodeDataStore[id].langs.filter(l => l !== lang);
            updateNodeCardDisplay(id);
            saveState();
        }

        function updateNodeCardDisplay(id) {
            const d = nodeDataStore[id];
            const area = document.getElementById(`stack-${id}`);
            let items = [];
            if(d.fw) items.push(d.fw);
            if(d.langs) items = [...items, ...d.langs];
            area.innerHTML = items.slice(0, 3).map(i => `<div class="tech-pill">${i}</div>`).join('');
        }

        function saveProjectData() {
            const diagram = {
                nodes: nodeDataStore,
                positions: Array.from(document.querySelectorAll('.node')).map(n => ({ id: n.id, left: n.style.left, top: n.style.top })),
                wires: instance.getAllConnections().map(c => ({ s: c.sourceId, t: c.targetId }))
            };
            const formData = new FormData();
            formData.append('diagram', JSON.stringify(diagram));
            formData.append('project_name', '<?php echo $project_name; ?>');

            fetch('save_handler.php', { method: 'POST', body: formData })
            .then(r => r.text())
            .then(res => alert(res.includes("Success") ? "✅ Saved Successfully!" : "❌ Error: " + res));
        }

        function exportJPG() {
            html2canvas(document.getElementById('canvas'), { backgroundColor: '#f5f5dc' }).then(canvas => {
                const link = document.createElement('a');
                link.download = '<?php echo $project_name; ?>.jpg';
                link.href = canvas.toDataURL("image/jpeg");
                link.click();
            });
        }

        function deleteSelected() { 
            if(selectedNode) { 
                instance.remove(selectedNode); 
                delete nodeDataStore[selectedNode.id]; 
                saveState();
                closePanel(); 
            }
        }
=======
        function renderGroup(label, options, key, id) {
            let h = `<div class="group-container"><span class="section-label">${label}</span>`;
            options.forEach(opt => { const checked = nodeDataStore[id][key].includes(opt) ? 'checked' : ''; h += `<label class="checkbox-item"><input type="checkbox" ${checked} onchange="updateCheck('${id}','${key}','${opt}',this.checked)"> ${opt}</label>`; });
            return h + `</div>`;
        }

        function renderSelect(label, options, key, id, current) {
            let h = `<div class="group-container"><span class="section-label">${label}</span><select class="config-select" onchange="updateNodeData('${id}','${key}',this.value)"><option value="">None</option>`;
            options.forEach(opt => { h += `<option value="${opt}" ${current === opt ? 'selected' : ''}>${opt}</option>`; });
            return h + `</select></div>`;
        }

        function updateCheck(id, key, val, checked) {
            if(checked) nodeDataStore[id][key].push(val); else nodeDataStore[id][key] = nodeDataStore[id][key].filter(v => v !== val);
            updateNodeCardDisplay(id); saveState();
        }

        function updateNodeData(id, k, v) {
            nodeDataStore[id][k] = v ? [v] : []; updateNodeCardDisplay(id); saveState();
        }

        function updateNodeCardDisplay(id) {
            const d = nodeDataStore[id]; const area = document.getElementById(`stack-${id}`);
            let items = [...d.langs, ...d.fw, ...d.styling, ...d.advanced].filter(i => i && i !== "");
            area.innerHTML = items.map(i => `<div class="tech-pill">${i}</div>`).join(''); instance.revalidate(id);
        }

        function getIcon(type) {
            const map = { frontend: 'fa-desktop', backend: 'fa-server', database: 'fa-database', cloud: 'fa-cloud', security: 'fa-shield-halved', ai: 'fa-robot', integration: 'fa-link' };
            return 'fa-solid ' + (map[type] || 'fa-cube');
        }

        window.onload = () => { if (savedProjectData && savedProjectData.nodes) {
            instance.deleteEveryConnection(); document.querySelectorAll('.node').forEach(n => instance.remove(n));
            nodeDataStore = savedProjectData.nodes;
            savedProjectData.positions.forEach(pos => { createNode(nodeDataStore[pos.id].type, getIcon(nodeDataStore[pos.id].type), pos.left, pos.top, pos.id, false); updateNodeCardDisplay(pos.id); });
            setTimeout(() => { savedProjectData.wires.forEach(w => instance.connect({ source: w.s, target: w.t })); }, 100);
        } };

        function drag(ev) { ev.dataTransfer.setData("type", ev.target.dataset.type); }
        function allowDrop(ev) { ev.preventDefault(); }
        function drop(ev) {
            ev.preventDefault(); const rect = canvas.getBoundingClientRect();
            const x = (ev.clientX - rect.left) / zoomLevel; const y = (ev.clientY - rect.top) / zoomLevel;
            createNode(ev.dataTransfer.getData("type"), getIcon(ev.dataTransfer.getData("type")), x + 'px', y + 'px');
        }
        function saveProjectData() {
            const diagram = { nodes: nodeDataStore, positions: Array.from(document.querySelectorAll('.node')).map(n => ({ id: n.id, left: n.style.left, top: n.style.top })), wires: instance.getAllConnections().map(c => ({ s: c.sourceId, t: c.targetId })) };
            const formData = new FormData(); formData.append('diagram', JSON.stringify(diagram)); formData.append('project_name', '<?php echo $project_name; ?>');
            fetch('save_handler.php', { method: 'POST', body: formData }).then(() => alert("✅ Architecture Saved!"));
        }
       async function exportJPG() {
            const nodesArr = document.querySelectorAll('.node');
            if (nodesArr.length === 0) { alert("Canvas is empty"); return; }
            let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
            nodesArr.forEach(node => {
                const l = parseInt(node.style.left); const t = parseInt(node.style.top);
                minX = Math.min(minX, l); minY = Math.min(minY, t);
                maxX = Math.max(maxX, l + 170); maxY = Math.max(maxY, t + node.offsetHeight);
            });
            const PAD = 100, SCALE = 2;
            const W = maxX - minX + PAD * 2, H = maxY - minY + PAD * 2;
            const exportCanvas = document.createElement('canvas');
            exportCanvas.width = W * SCALE; exportCanvas.height = H * SCALE;
            const ctx = exportCanvas.getContext('2d');
            ctx.scale(SCALE, SCALE);
            ctx.fillStyle = '#f5f5dc'; ctx.fillRect(0, 0, W, H);
            
            instance.getAllConnections().forEach(c => {
                const s = c.source, t = c.target;
                const fx = parseInt(s.style.left) - minX + PAD + s.offsetWidth, fy = parseInt(s.style.top) - minY + PAD + 55;
                const tx = parseInt(t.style.left) - minX + PAD, ty = parseInt(t.style.top) - minY + PAD + 55;
                const dx = Math.abs(tx - fx) * 0.6 + 40;
                ctx.strokeStyle = '#8b795e'; ctx.lineWidth = 2.5; ctx.globalAlpha = 0.7;
                ctx.beginPath(); ctx.moveTo(fx, fy); ctx.bezierCurveTo(fx + dx, fy, tx - dx, ty, tx, ty); ctx.stroke();
                ctx.fillStyle = '#8b795e'; ctx.beginPath(); ctx.arc(tx, ty, 4, 0, Math.PI*2); ctx.fill();
            });

            nodesArr.forEach(node => {
                const nx = parseInt(node.style.left) - minX + PAD, ny = parseInt(node.style.top) - minY + PAD;
                const nw = node.offsetWidth, nh = node.offsetHeight;
                ctx.fillStyle = '#ffffff'; ctx.beginPath(); ctx.roundRect(nx, ny, nw, nh, 12); ctx.fill();
                ctx.strokeStyle = '#8b795e'; ctx.stroke();
                ctx.fillStyle = '#8b795e'; ctx.font = 'bold 10px Arial';
                ctx.fillText(node.querySelector('.type-label').innerText.toUpperCase(), nx + 10, ny + 18);
                node.querySelectorAll('.tech-pill').forEach((p, i) => {
                    ctx.fillStyle = '#f1f5f9'; ctx.beginPath(); ctx.roundRect(nx + 10, ny + 30 + (i * 18), nw - 20, 15, 4); ctx.fill();
                    ctx.fillStyle = '#475569'; ctx.font = 'bold 8.5px Arial'; ctx.fillText(p.innerText, nx + 15, ny + 41 + (i * 18));
                });
            });
            const link = document.createElement('a'); link.download = 'blueprint.jpg';
            link.href = exportCanvas.toDataURL("image/jpeg", 0.95); link.click();
        }

        function deleteSelected() { if (selectedNode) { instance.remove(selectedNode); delete nodeDataStore[selectedNode.id]; saveState(); closePanel(); } }
>>>>>>> c631a520ebf19dbf936074dbab1d7ccc72e9f24f
        function closePanel() { document.getElementById('details-panel').style.display = 'none'; if(selectedNode) selectedNode.classList.remove('selected'); selectedNode = null; }
    </script>
</body>
</html>