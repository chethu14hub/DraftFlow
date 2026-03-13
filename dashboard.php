<?php
session_start();
require_once('db_config.php');
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DraftBoard Pro | System Architect Studio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/leader-line-new@1.1.9/dist/leader-line.min.js"></script>
    
    <style>
        :root { --cream: #f5f5dc; --bronze: #8b795e; --dark: #121212; --white: #ffffff; --accent: #3b82f6; }
        body { margin: 0; display: flex; height: 100vh; font-family: 'Inter', sans-serif; background: var(--cream); overflow: hidden; }

        .sidebar { width: 250px; background: var(--dark); color: white; padding: 20px; z-index: 1000; border-right: 1px solid #333; }
        .sidebar h2 { font-size: 14px; letter-spacing: 3px; color: var(--bronze); margin-bottom: 25px; border-bottom: 1px solid #333; padding-bottom: 10px; text-align: center; }
        .tool-item { padding: 12px; background: #1e1e1e; border-radius: 8px; margin-bottom: 10px; cursor: grab; display: flex; align-items: center; gap: 12px; transition: 0.2s; font-size: 13px; }
        .tool-item:hover { background: var(--bronze); color: white; }

        .main-stage { flex: 1; position: relative; display: flex; flex-direction: column; }
        .toolbar { background: white; padding: 10px 25px; border-bottom: 1px solid #ddd; display: flex; gap: 15px; align-items: center; min-height: 50px; z-index: 500; }
        
        /* THE CRITICAL CSS FIX FOR WIRES */
        #canvas { flex: 1; position: relative; background-image: radial-gradient(#d1d1b3 1px, transparent 1px); background-size: 30px 30px; overflow: visible !important; }

        .node { 
            position: absolute; width: 125px; min-height: 85px; padding: 12px; background: white; 
            border: 1px solid var(--bronze); border-radius: 10px; text-align: center; cursor: move; z-index: 100;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; flex-direction: column; align-items: center;
        }
        .node.selected { outline: 3px solid var(--accent); }
        .node i { font-size: 28px; color: var(--dark); pointer-events: none; }
        .node span.type-label { display: block; font-size: 9px; margin-top: 4px; font-weight: 800; text-transform: uppercase; color: var(--bronze); pointer-events: none; }
        
        .node-stack-display { font-size: 8px; font-weight: 600; color: #666; margin-top: 5px; display: flex; flex-wrap: wrap; justify-content: center; gap: 2px; pointer-events: none; }
        .tech-pill { background: #eee; padding: 1px 4px; border-radius: 3px; border: 1px solid #ddd; }

        /* THE BRONZE DOT CONNECTION POINT */
        .dot { width: 14px; height: 14px; background: var(--bronze); border-radius: 50%; position: absolute; right: -7px; top: 35px; cursor: crosshair; z-index: 110; border: 2px solid white; }

        #context-menu {
            display: none; position: fixed; background: white; border: 1px solid #ccc; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 3000; min-width: 180px; border-radius: 8px; overflow: hidden;
        }
        #context-menu .menu-header { background: #f0f0f0; padding: 8px 12px; font-size: 10px; font-weight: bold; color: #666; border-bottom: 1px solid #ddd; }
        #context-menu div.menu-item { padding: 10px 15px; cursor: pointer; font-size: 13px; color: #333; display: flex; align-items: center; gap: 10px; }
        #context-menu div.menu-item:hover { background: var(--bronze); color: white; }

        .details-panel { width: 380px; background: white; border-left: 1px solid #ddd; display: none; padding: 25px; overflow-y: auto; z-index: 1001; }
        .section-label { font-size: 11px; font-weight: 800; color: var(--bronze); margin: 15px 0 5px; display: block; text-transform: uppercase; }
        
        select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 12px; font-size: 13px; }
        .checkbox-group { background: #f9f9f9; padding: 10px; border-radius: 6px; border: 1px solid #eee; margin-bottom: 12px; }
        .checkbox-item { display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 5px; cursor: pointer; }
        .btn-tool { background: #f8f8f8; border: 1px solid #ddd; padding: 7px 14px; cursor: pointer; border-radius: 6px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 8px; }

        /* FORCE WIRES TO BE VISIBLE */
        .leader-line { z-index: 9999 !important; }
    </style>
</head>
<body onclick="closeContextMenu()">

    <div class="sidebar">
        <h2>TOOLBOX</h2>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="frontend"><i class="fa-solid fa-desktop"></i> Frontend</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="backend"><i class="fa-solid fa-server"></i> Backend</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="database"><i class="fa-solid fa-database"></i> Database</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="cloud"><i class="fa-solid fa-cloud"></i> Infrastructure</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="devops"><i class="fa-solid fa-infinity"></i> DevOps</div>
        <div class="tool-item" draggable="true" ondragstart="drag(event)" data-type="security"><i class="fa-solid fa-shield-halved"></i> Security</div>
    </div>

    <div class="main-stage">
        <div class="toolbar">
            <button class="btn-tool" onclick="deleteSelected()" style="color:#dc2626;"><i class="fa-solid fa-trash"></i> Delete Selected</button>
            <span style="margin-left:auto; font-size:11px; color:#666; font-weight:600;">Right-click node to wire | Ctrl+Z (Undo)</span>
        </div>
        <div id="canvas" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
    </div>

    <div id="context-menu"></div>

    <div class="details-panel" id="details-panel">
        <h3 id="panel-title">Configuration</h3>
        <div id="dynamic-fields"></div>
        <button onclick="closePanel()" style="width:100%; padding:14px; background:var(--dark); color:white; border:none; cursor:pointer; border-radius:8px; margin-top:15px; font-weight:bold;">Update Component</button>
    </div>

    <script>
        let connections = [];
        let history = [];
        let nodeDataStore = {}; 
        let selectedNode = null;

        // --- YOUR UNDO/SAVE SYSTEM ---
        function saveState() {
            const state = {
                nodes: Array.from(document.querySelectorAll('.node')).map(n => ({
                    id: n.id, type: n.dataset.type, icon: n.querySelector('i').className, 
                    left: n.style.left, top: n.style.top, data: JSON.parse(JSON.stringify(nodeDataStore[n.id] || {}))
                })),
                lines: connections.map(c => ({ start: c.start, end: c.end }))
            };
            history.push(JSON.stringify(state));
            if(history.length > 50) history.shift();
        }

        function undo() { 
            if (history.length > 1) { 
                history.pop(); 
                renderState(history[history.length - 1]); 
            } 
        }

        function renderState(json) {
            const state = JSON.parse(json);
            connections.forEach(c => c.line.remove());
            connections = [];
            document.getElementById('canvas').innerHTML = "";
            nodeDataStore = {};
            state.nodes.forEach(n => {
                createNode(n.type, n.icon, n.left, n.top, n.id);
                nodeDataStore[n.id] = n.data;
                updateNodeCardDisplay(n.id);
            });
            state.lines.forEach(l => {
                const s = document.getElementById(l.start);
                const e = document.getElementById(l.end);
                if(s && e) createLine(s, e);
            });
        }

        // --- YOUR DRAG/DROP LOGIC ---
        function allowDrop(ev) { ev.preventDefault(); }
        function drag(ev) { 
            ev.dataTransfer.setData("type", ev.target.dataset.type); 
            ev.dataTransfer.setData("icon", ev.target.querySelector('i').className); 
        }
        
        function drop(ev) {
            ev.preventDefault();
            const rect = document.getElementById('canvas').getBoundingClientRect();
            createNode(ev.dataTransfer.getData("type"), ev.dataTransfer.getData("icon"), (ev.clientX - rect.left - 60) + 'px', (ev.clientY - rect.top - 40) + 'px');
            saveState();
        }

        function createNode(type, icon, left, top, existingId = null) {
            const id = existingId || "n" + Date.now();
            const div = document.createElement('div');
            div.className = 'node'; div.id = id; div.dataset.type = type;
            div.style.left = left; div.style.top = top;
            
            // Fixed InnerHTML with your Tech Pills and Dot
            div.innerHTML = `<i class="${icon}"></i><span class="type-label">${type}</span><div class="node-stack-display" id="stack-${id}"></div><div class="dot" id="dot-${id}"></div>`;
            
            div.onclick = (e) => {
                if(selectedNode) selectedNode.classList.remove('selected');
                selectedNode = div; div.classList.add('selected');
                openDetails(id, type);
                e.stopPropagation();
            };

            div.oncontextmenu = (e) => {
                e.preventDefault();
                showContextMenu(e, div);
            };

            document.getElementById('canvas').appendChild(div);
            makeDraggable(div);
            if(!nodeDataStore[id]) nodeDataStore[id] = { type: type, langs: [], fw: "" };
            return div;
        }

        function makeDraggable(el) {
            let p1 = 0, p2 = 0, p3 = 0, p4 = 0;
            el.onmousedown = (e) => {
                p3 = e.clientX; p4 = e.clientY;
                document.onmousemove = (ev) => {
                    p1 = p3 - ev.clientX; p2 = p4 - ev.clientY; p3 = ev.clientX; p4 = ev.clientY;
                    el.style.top = (el.offsetTop - p2) + "px"; el.style.left = (el.offsetLeft - p1) + "px";
                    
                    // REFRESH WIRES DURING DRAG
                    connections.forEach(c => { if(c.line) c.line.position(); });
                };
                document.onmouseup = () => { document.onmousemove = null; saveState(); };
            };
        }

        // --- YOUR CONTEXT MENU ---
        function showContextMenu(e, sourceNode) {
            const menu = document.getElementById('context-menu');
            const otherNodes = Array.from(document.querySelectorAll('.node')).filter(n => n.id !== sourceNode.id);
            
            if(otherNodes.length === 0) {
                menu.innerHTML = `<div class="menu-header">No nodes available</div>`;
            } else {
                let html = `<div class="menu-header">WIRE TO:</div>`;
                otherNodes.forEach(n => {
                    html += `<div class="menu-item" onclick="handleConnect('${sourceNode.id}', '${n.id}')">
                                <i class="${n.querySelector('i').className}"></i> ${n.dataset.type.toUpperCase()}
                             </div>`;
                });
                menu.innerHTML = html;
            }
            menu.style.display = 'block';
            menu.style.left = e.clientX + 'px';
            menu.style.top = e.clientY + 'px';
        }

        function handleConnect(sourceId, targetId) {
            createLine(document.getElementById(sourceId), document.getElementById(targetId));
            saveState();
            closeContextMenu();
        }

        // --- THE WIRE FUNCTION ---
        function createLine(s, t) {
            try {
                const startDot = document.getElementById('dot-' + s.id);
                const line = new LeaderLine(
                    startDot, 
                    t, 
                    { color: '#8b795e', size: 2, path: 'grid', endPlug: 'arrow', startSocket: 'right', endSocket: 'left' }
                );
                connections.push({ line, start: s.id, end: t.id });
                window.dispatchEvent(new Event('resize'));
            } catch (err) { console.error("Wiring failed:", err); }
        }

        function closeContextMenu() { document.getElementById('context-menu').style.display = 'none'; }

        // --- YOUR PROPERTY PANEL LOGIC ---
        function openDetails(id, type) {
            const panel = document.getElementById('details-panel');
            const fields = document.getElementById('dynamic-fields');
            panel.style.display = 'block';
            document.getElementById('panel-title').innerText = type.toUpperCase() + " CONFIG";
            let html = ""; const d = nodeDataStore[id];
            if(type === 'frontend') {
                html += field("🔹 Languages", "checkbox", ["HTML", "CSS", "JS", "TS"], d.langs || [], id, 'langs');
                html += field("🔹 Framework", "select", ["None", "React", "Vue", "Angular", "Next.js"], d.fw, id, 'fw');
            } else if(type === 'backend') {
                html += field("🔹 Languages", "checkbox", ["PHP", "Node.js", "Python", "Go"], d.langs || [], id, 'langs');
                html += field("🔹 Framework", "select", ["None", "Laravel", "Express", "Django"], d.fw, id, 'fw');
            } else if(type === 'database') {
                html += field("🔹 Engine", "select", ["MySQL", "PostgreSQL", "MongoDB", "Redis"], d.fw, id, 'fw');
            } else {
                html += field("🔹 Options", "checkbox", ["Active", "Internal", "External"], d.langs || [], id, 'langs');
            }
            fields.innerHTML = html;
        }

        function field(label, tag, opts, val, nodeId, key) {
            let h = `<span class="section-label">${label}</span>`;
            if(tag === 'select') {
                h += `<select onchange="updateData('${nodeId}','${key}',this.value)">`;
                opts.forEach(o => h += `<option value="${o==='None'?'':o}" ${val===o?'selected':''}>${o}</option>`);
                h += `</select>`;
            } else {
                h += `<div class="checkbox-group">`;
                opts.forEach(o => h += `<label class="checkbox-item"><input type="checkbox" ${Array.isArray(val)&&val.includes(o)?'checked':''} onchange="updateCheck('${nodeId}','${key}','${o}',this.checked)"> ${o}</label>`);
                h += `</div>`;
            }
            return h;
        }

        function updateData(id, k, v) { nodeDataStore[id][k] = v; updateNodeCardDisplay(id); saveState(); }
        function updateCheck(id, k, v, c) { if(!nodeDataStore[id][k]) nodeDataStore[id][k] = []; if(c) nodeDataStore[id][k].push(v); else nodeDataStore[id][k] = nodeDataStore[id][k].filter(i=>i!==v); updateNodeCardDisplay(id); saveState(); }
        function updateNodeCardDisplay(id) { const d = nodeDataStore[id]; const disp = document.getElementById(`stack-${id}`); let items = []; if(d.fw) items.push(d.fw); if(d.langs) items = [...items, ...d.langs]; if(disp) disp.innerHTML = items.slice(0, 3).map(i => `<div class="tech-pill">${i}</div>`).join('') + (items.length > 3 ? `<span>+${items.length-3}</span>` : ''); }
        
        function deleteSelected() { 
            if(!selectedNode) return; 
            connections = connections.filter(c => { if(c.start===selectedNode.id || c.end===selectedNode.id){ c.line.remove(); return false; } return true; }); 
            delete nodeDataStore[selectedNode.id]; 
            selectedNode.remove(); 
            selectedNode = null; 
            saveState(); 
            closePanel(); 
        }

        function closePanel() { document.getElementById('details-panel').style.display = 'none'; if(selectedNode) selectedNode.classList.remove('selected'); selectedNode = null; }

        window.onload = () => saveState();
        window.onkeydown = (e) => { if(e.ctrlKey && e.key === 'z') undo(); if(e.key === 'Delete') deleteSelected(); };
    </script>
</body>
</html>