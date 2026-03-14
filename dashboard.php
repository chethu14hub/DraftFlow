<?php
session_start();
require_once('db_config.php');
if (!isset($_SESSION['user_id'])) { header("Location: db_index.php"); exit(); }

$user_name = $_SESSION['user_name'] ?? "Architect";
$user_id = $_SESSION['user_id'];
$project_name = $_GET['project'] ?? "New System Architecture";

// --- LOAD DATA FROM DATABASE ---
$saved_data = "{}"; 
$stmt = $conn->prepare("SELECT project_data FROM projects WHERE user_id = ? AND project_name = ?");
$stmt->bind_param("is", $user_id, $project_name);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $saved_data = $row['project_data'];
}
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
        .btn-save { background: #0f172a; color: white; border: none; }
    </style>
</head>
<body>

    <div class="sidebar">
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
    </div>

    <div class="main-stage">
        <div class="project-header">
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
        </div>
    </div>

    <script>
        let nodeDataStore = {};
        let selectedNode = null;
        let undoStack = [];
        let redoStack = [];

        const savedProjectData = <?php echo $saved_data; ?>;

        let instance = jsPlumb.getInstance({
            Connector: ["Flowchart", { stub: 40, cornerRadius: 5 }],
            PaintStyle: { stroke: "#8b795e", strokeWidth: 2.5 },
            Endpoint: ["Dot", { radius: 5 }],
            EndpointStyle: { fill: "#8b795e" },
            Container: "canvas"
        });

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
            }
            fields.innerHTML = html;
        }

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
        function closePanel() { document.getElementById('details-panel').style.display = 'none'; if(selectedNode) selectedNode.classList.remove('selected'); selectedNode = null; }
    </script>
</body>
</html>