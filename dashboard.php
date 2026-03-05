<?php
session_start();
require_once('db_config.php');
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DraftBoard Pro | System Architect</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --cream: #f5f5dc; --bronze: #8b795e; --dark: #2d2419; --white: #ffffff; }
        body { margin: 0; display: flex; height: 100vh; font-family: 'Inter', sans-serif; background: var(--cream); overflow: hidden; }

        /* --- LEFT TOOLBAR --- */
        .sidebar { width: 220px; background: var(--dark); color: var(--cream); padding: 20px; display: flex; flex-direction: column; gap: 10px; }
        .sidebar h2 { font-size: 16px; letter-spacing: 3px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; margin-bottom: 20px; }
        .tool-item { 
            padding: 12px; background: rgba(255,255,255,0.05); border-radius: 6px; 
            cursor: grab; display: flex; align-items: center; gap: 10px; transition: 0.3s; font-size: 13px;
        }
        .tool-item:hover { background: var(--bronze); }

        /* --- CENTRAL CANVAS --- */
        .main-stage { flex: 1; position: relative; display: flex; flex-direction: column; }
        #canvas { 
            flex: 1; background-image: radial-gradient(var(--bronze) 0.5px, transparent 0.5px); 
            background-size: 30px 30px; position: relative; overflow: auto; 
        }

        /* --- PLACED NODES --- */
        .node { 
            position: absolute; width: 80px; text-align: center; cursor: move; 
            padding: 10px; border-radius: 8px; transition: transform 0.2s;
        }
        .node i { font-size: 30px; color: var(--dark); display: block; margin-bottom: 5px; }
        .node span { font-size: 10px; font-weight: bold; text-transform: uppercase; color: var(--dark); }
        .node.selected { outline: 2px solid var(--bronze); background: rgba(139, 121, 94, 0.1); }

        /* --- RIGHT DETAILS PANEL --- */
        .details-panel { 
            width: 350px; background: var(--white); border-left: 1px solid #ddd; 
            padding: 20px; display: none; overflow-y: auto; box-shadow: -5px 0 15px rgba(0,0,0,0.05);
        }
        .details-panel h3 { color: var(--dark); border-bottom: 2px solid var(--cream); padding-bottom: 10px; }
        .detail-group { margin-bottom: 20px; }
        .detail-group label { font-size: 11px; font-weight: bold; color: var(--bronze); display: block; margin-bottom: 5px; }
        select, textarea, input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; }
        textarea { height: 80px; font-family: monospace; background: #f9f9f9; }

        .btn-save { background: var(--dark); color: var(--cream); border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>LIBRARY</h2>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" id="frontend" data-type="frontend"><i class="fa-solid fa-desktop"></i> Frontend</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" id="backend" data-type="backend"><i class="fa-solid fa-gears"></i> Backend</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" id="database" data-type="database"><i class="fa-solid fa-database"></i> Database</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" id="cloud" data-type="cloud"><i class="fa-solid fa-cloud"></i> Cloud / Infra</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" id="devops" data-type="devops"><i class="fa-solid fa-infinity"></i> DevOps</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" id="security" data-type="security"><i class="fa-solid fa-shield-halved"></i> Security</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" id="integration" data-type="integration"><i class="fa-solid fa-link"></i> Integration</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" id="users" data-type="users"><i class="fa-solid fa-users"></i> Users</div>
    </div>

    <div class="main-stage">
        <div style="background: white; padding: 10px 30px; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid #ddd;">
            <strong>ARCHITECTURE BLUEPRINT</strong>
            <button class="btn-save" onclick="exportData()">FINALIZE SYSTEM</button>
        </div>
        <div id="canvas" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
    </div>

    <div class="details-panel" id="details-panel">
        <h3 id="panel-title">Component Specs</h3>
        <div id="dynamic-fields"></div>
        <button class="btn-save" style="width:100%" onclick="closePanel()">UPDATE COMPONENT</button>
    </div>

    <script>
        let selectedNode = null;
        let nodeData = {}; // Stores information for each node on canvas

        function allowDrop(ev) { ev.preventDefault(); }
        function drag(ev) { ev.dataTransfer.setData("type", ev.target.dataset.type); ev.dataTransfer.setData("icon", ev.target.querySelector('i').className); }

        function drop(ev) {
            ev.preventDefault();
            const type = ev.dataTransfer.getData("type");
            const icon = ev.dataTransfer.getData("icon");
            const id = "node_" + Date.now();
            
            const x = ev.clientX - document.getElementById('canvas').getBoundingClientRect().left - 40;
            const y = ev.clientY - document.getElementById('canvas').getBoundingClientRect().top - 40;

            const div = document.createElement('div');
            div.className = 'node';
            div.id = id;
            div.style.left = x + 'px';
            div.style.top = y + 'px';
            div.innerHTML = `<i class="${icon}"></i><span>${type}</span>`;
            
            // Interaction: Click to show details, drag to move
            div.onclick = (e) => { e.stopPropagation(); openPanel(id, type); };
            makeDraggable(div);
            
            document.getElementById('canvas').appendChild(div);
            nodeData[id] = { type: type, specs: {} };
        }

        function openPanel(id, type) {
            if(selectedNode) selectedNode.classList.remove('selected');
            selectedNode = document.getElementById(id);
            selectedNode.classList.add('selected');

            const panel = document.getElementById('details-panel');
            const fields = document.getElementById('dynamic-fields');
            panel.style.display = 'block';
            document.getElementById('panel-title').innerText = type.toUpperCase() + " CONFIG";
            
            fields.innerHTML = generateFields(type, id);
        }

        function generateFields(type, id) {
            const current = nodeData[id].specs;
            let html = "";
            
            if(type === 'frontend') {
                html += field("UI Type", "select", ["Web App", "Mobile App", "Desktop App"], current.ui, id, 'ui');
                html += field("Framework", "select", ["React", "Angular", "Vue.js", "Next.js"], current.fw, id, 'fw');
                html += field("Code Snippet", "textarea", "Enter logic...", current.code, id, 'code');
            } else if(type === 'backend') {
                html += field("Language", "select", ["PHP", "Node.js", "Python", "Java"], current.lang, id, 'lang');
                html += field("API Type", "select", ["REST", "GraphQL", "gRPC"], current.api, id, 'api');
                html += field("Controller Logic", "textarea", "Enter logic...", current.code, id, 'code');
            } else if(type === 'database') {
                html += field("Engine", "select", ["MySQL", "PostgreSQL", "MongoDB", "Redis"], current.eng, id, 'eng');
                html += field("Schema Description", "textarea", "Define tables...", current.schema, id, 'schema');
            }
            // Add more conditions for Cloud, DevOps, etc.
            return html;
        }

        function field(label, tag, options, val, nodeId, key) {
            let input = "";
            if(tag === 'select') {
                input = `<select onchange="updateSpec('${nodeId}', '${key}', this.value)">${options.map(o => `<option ${val===o?'selected':''}>${o}</option>`).join('')}</select>`;
            } else {
                input = `<textarea onkeyup="updateSpec('${nodeId}', '${key}', this.value)" placeholder="${options}">${val || ''}</textarea>`;
            }
            return `<div class="detail-group"><label>${label}</label>${input}</div>`;
        }

        function updateSpec(id, key, val) { nodeData[id].specs[key] = val; }
        function closePanel() { document.getElementById('details-panel').style.display = 'none'; }

        function makeDraggable(el) {
            let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
            el.onmousedown = dragMouseDown;
            function dragMouseDown(e) { e.preventDefault(); pos3 = e.clientX; pos4 = e.clientY; document.onmouseup = closeDragElement; document.onmousemove = elementDrag; }
            function elementDrag(e) { pos1 = pos3 - e.clientX; pos2 = pos4 - e.clientY; pos3 = e.clientX; pos4 = e.clientY; el.style.top = (el.offsetTop - pos2) + "px"; el.style.left = (el.offsetLeft - pos1) + "px"; }
            function closeDragElement() { document.onmouseup = null; document.onmousemove = null; }
        }

        function exportData() {
            console.log("Saving full system architecture:", nodeData);
            alert("Workflow captured! Admin can now view this full technical stack.");
        }
    </script>
</body>
</html>