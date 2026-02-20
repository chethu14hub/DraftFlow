const canvas = document.getElementById('canvas-container');
const aiMessages = document.getElementById('ai-messages');

// Handle dragging components from sidebar to canvas
document.querySelectorAll('.draggable-item').forEach(item => {
    item.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('type', e.target.getAttribute('data-type'));
    });
});

canvas.addEventListener('dragover', (e) => e.preventDefault());

canvas.addEventListener('drop', (e) => {
    e.preventDefault();
    const type = e.dataTransfer.getData('type');
    
    // Create a visual "Node" on the canvas
    const newNode = document.createElement('div');
    newNode.className = 'node shadow-sm';
    newNode.innerText = type;
    newNode.style.left = `${e.offsetX}px`;
    newNode.style.top = `${e.offsetY}px`;
    
    canvas.appendChild(newNode);
    
    // Simple notification to AI panel when a component is added
    updateAI(`You added a ${type} component. Ensure your API connects to the Database securely.`);
});

function updateAI(message) {
    const p = document.createElement('p');
    p.innerHTML = `<b>AI:</b> ${message}`;
    p.className = "small mb-2";
    aiMessages.appendChild(p);
    aiMessages.scrollTop = aiMessages.scrollHeight;
}

function askAI() {
    const input = document.getElementById('userInput');
    if(input.value) {
        updateAI(`Thinking about: "${input.value}"...`);
        input.value = "";
        // Your friend will connect this to PHP/Gemini API later
    }
}