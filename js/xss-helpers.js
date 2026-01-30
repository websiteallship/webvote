// Helper function để escape HTML trong JavaScript
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Helper function để tạo element an toàn
function createSafeElement(tag, props = {}, children = []) {
    const el = document.createElement(tag);

    // Set properties
    Object.keys(props).forEach(key => {
        if (key === 'className') {
            el.className = props[key];
        } else if (key === 'textContent') {
            el.textContent = props[key];
        } else if (key === 'onclick') {
            el.onclick = props[key];
        } else {
            el.setAttribute(key, props[key]);
        }
    });

    // Append children
    children.forEach(child => {
        if (typeof child === 'string') {
            el.appendChild(document.createTextNode(child));
        } else {
            el.appendChild(child);
        }
    });

    return el;
}
