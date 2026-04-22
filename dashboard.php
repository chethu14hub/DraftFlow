<?php
session_start();
require_once('db_config.php');
// Use this function to actually load the values into $_ENV
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

loadEnv(__DIR__ . '/.env');
$apiKey = $_ENV['GROQ_API_KEY'] ?? '';







if (!isset($_SESSION['user_id'])) { header("Location: db_index.php"); exit(); }

$user_name = $_SESSION['user_name'] ?? "Architect";
$user_id = $_SESSION['user_id'];
$project_name = $_GET['project'] ?? "New System Architecture";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'chat') {
    // Clear any previous output to ensure only JSON is sent
    ob_clean();
    header('Content-Type: application/json');

    // Get the raw input
    $input = json_decode(file_get_contents('php://input'), true);
    $userMsg = $input['message'] ?? '';

    // Your working API details
    $apiKey = $_ENV['GROQ_API_KEY'];
    $url = 'https://api.groq.com/openai/v1/chat/completions';
    // Define the persona based on your specific project needs

$systemInstructions = "You are the DraftFlow Guide for $user_name. 
Your knowledge is STRICTLY limited to the following project information:

1. Project Overview: DraftFlow  is a software architecture design tool.
2. How to use:
   - Drag modules (Frontend, Backend, Database, etc.) from the left sidebar onto the canvas.
   - Click on a placed node to configure its tech stack.
   - Drag from the bronze dots on a node to connect it to another node.
   - Use the 'Save' button to store your architecture.
   - Use 'Export' to download the design as a blueprint.
   -if user greet  react as greeting

STRICT RULES:
- Only provide information about DraftFlow  and the drag-and-drop steps above.
- If the user asks about anything else (e.g., general coding, food, weather, personal questions), you MUST respond exactly with: 'For further inquiries or support, please contact draftflow@gmail.com'. 
- Do not provide any other helpful advice or external information.";

$data = [
    'model' => 'llama-3.3-70b-versatile',
    'messages' => [
        ['role' => 'system', 'content' => $systemInstructions],
        ['role' => 'user', 'content' => $userMsg]
    ],
    'temperature' => 0.9 // Lower is more focused, higher is more creative
];

   

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);

    // THE CRITICAL FIXES FROM TEST_AI.PHP
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(['reply' => 'Connection Error: ' . curl_error($ch)]);
    } else {
        $result = json_decode($response, true);
        // Extract the reply and send it back
        $aiReply = $result['choices'][0]['message']['content'] ?? 'AI error: No text in response';
        echo json_encode(['reply' => $aiReply]);
    }

    curl_close($ch);
    exit; // Stop everything else so HTML doesn't leak into the JSON
}

$saved_data = "{}"; 
$stmt = $conn->prepare("SELECT project_data FROM projects WHERE user_id = ? AND project_name = ?");
$stmt->bind_param("is", $user_id, $project_name);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) { $saved_data = $row['project_data']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DraftFlow Pro | <?php echo htmlspecialchars($project_name); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsPlumb/2.15.6/js/jsplumb.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <style>
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
        .btn-save { background: #0f172a; color: white; border: none; }
    </style>
</head>
<body>

    <div class="sidebar">
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
    </div>

    <div class="main-stage">
        <div class="project-header">
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
        </div>
    </div>

    <script>
        let nodeDataStore = {};
        let selectedNode = null;
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
            EndpointStyle: { fill: "#8b795e" },
            Container: "canvas"
        });

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

    // Show User Message
    const uDiv = document.createElement('div'); 
    uDiv.className = 'message user-msg'; 
    uDiv.textContent = userMsg; 
    chatBody.appendChild(uDiv);
    
    input.value = ''; 
    chatBody.scrollTop = chatBody.scrollHeight;

    // Show Typing Indicator
    const typingDiv = document.createElement('div'); 
    typingDiv.className = 'message bot-msg'; 
    typingDiv.textContent = "..."; 
    chatBody.appendChild(typingDiv);

    try {
        const response = await fetch('dashboard.php?action=chat', { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' }, 
            body: JSON.stringify({ message: userMsg }) 
        });

        const data = await response.json();

        // FIX: Use 'data.reply' instead of the long 'choices' path
        if (data.reply) {
            typingDiv.innerHTML = data.reply.replace(/\n/g, '<br>');
        } else {
            typingDiv.textContent = "Error: AI sent an empty response.";
        }

    } catch (error) { 
        console.error("Fetch Error:", error); 
        typingDiv.textContent = "AI unreachable."; 
    }
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
            }
            fields.innerHTML = html;
        }

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
        function closePanel() { document.getElementById('details-panel').style.display = 'none'; if(selectedNode) selectedNode.classList.remove('selected'); selectedNode = null; }
    
    </script>
</body>
</html>