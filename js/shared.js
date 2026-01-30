/**
 * Shared JavaScript utilities for ALLSHIP GALA DINNER Voting System
 * Prevents code duplication across vote.js, live.js, and admin.js
 */

// ============================================
// GLOBAL STATE
// ============================================
let serverTimeOffset = 0;
let sessionCountdownInterval = null;

// ============================================
// SERVER TIME SYNCHRONIZATION
// ============================================

/**
 * Sync client time with server time
 * Calculates offset to correct for clock differences
 */
async function syncServerTime() {
    try {
        const clientTimeBefore = Date.now();
        const res = await fetch('api/server_time.php');
        const data = await res.json();
        const clientTimeAfter = Date.now();

        // Estimate network latency
        const latency = (clientTimeAfter - clientTimeBefore) / 2;
        const serverTime = data.server_time * 1000;
        const clientTime = clientTimeBefore + latency;

        // Calculate offset (server time - client time)
        serverTimeOffset = serverTime - clientTime;

        console.log('Server time synced. Offset:', serverTimeOffset, 'ms');
    } catch (error) {
        console.error('Failed to sync server time:', error);
    }
}

/**
 * Get current time adjusted with server offset
 */
function getServerAdjustedTime() {
    return Date.now() + serverTimeOffset;
}

// ============================================
// SESSION MANAGEMENT
// ============================================

/**
 * Load session data from server
 * @returns {Promise<Object>} Session data
 */
async function fetchSession() {
    const res = await fetch('api/session.php');
    return await res.json();
}

/**
 * Parse datetime string from PHP (Y-m-d H:i:s) to timestamp
 * @param {string} dateTimeStr - PHP datetime string
 * @returns {number} Timestamp in milliseconds
 */
function parsePhpDateTime(dateTimeStr) {
    if (!dateTimeStr) return 0;
    const endTimeStr = dateTimeStr.replace(' ', 'T');
    return new Date(endTimeStr).getTime();
}

/**
 * Calculate remaining time until end
 * @param {string} endTimeStr - PHP datetime string
 * @returns {number} Remaining milliseconds
 */
function calculateRemaining(endTimeStr) {
    const serverNow = getServerAdjustedTime();
    const end = parsePhpDateTime(endTimeStr);
    return Math.max(0, end - serverNow);
}

/**
 * Format milliseconds to MM:SS string
 * @param {number} ms - Milliseconds
 * @returns {string} Formatted time string
 */
function formatCountdown(ms) {
    const minutes = Math.floor(ms / 60000);
    const seconds = Math.floor((ms % 60000) / 1000);
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
}

/**
 * Update countdown display
 * @param {string} endTime - PHP datetime string
 * @param {string} displayElementId - ID of display element
 * @param {function} [onExpired] - Callback when countdown expires
 */
function updateCountdown(endTime, displayElementId, onExpired) {
    const remaining = calculateRemaining(endTime);

    const display = document.getElementById(displayElementId);
    if (display) {
        display.textContent = formatCountdown(remaining);
    }

    if (remaining <= 0 && sessionCountdownInterval) {
        clearInterval(sessionCountdownInterval);
        sessionCountdownInterval = null;
        if (typeof onExpired === 'function') {
            onExpired();
        }
    }
}

/**
 * Start countdown interval
 * @param {string} endTime - PHP datetime string
 * @param {string} displayElementId - ID of display element
 * @param {function} [onExpired] - Callback when countdown expires
 */
function startCountdown(endTime, displayElementId, onExpired) {
    // Clear existing interval
    if (sessionCountdownInterval) {
        clearInterval(sessionCountdownInterval);
    }

    // Initial update
    updateCountdown(endTime, displayElementId, onExpired);

    // Start interval
    sessionCountdownInterval = setInterval(() => {
        updateCountdown(endTime, displayElementId, onExpired);
    }, 1000);
}

/**
 * Stop countdown
 */
function stopCountdown() {
    if (sessionCountdownInterval) {
        clearInterval(sessionCountdownInterval);
        sessionCountdownInterval = null;
    }
}

// ============================================
// XSS PROTECTION
// ============================================

/**
 * Escape HTML to prevent XSS
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Create a safe text element
 * @param {string} tag - HTML tag
 * @param {string} text - Text content
 * @param {string} className - CSS class
 * @returns {HTMLElement}
 */
function createTextElement(tag, text, className = '') {
    const el = document.createElement(tag);
    el.textContent = text;
    if (className) el.className = className;
    return el;
}

// ============================================
// TOAST NOTIFICATIONS
// ============================================

/**
 * Show toast notification
 * @param {string} type - 'success', 'error', 'info', 'warning'
 * @param {string} title - Toast title
 * @param {string} message - Toast message
 */
function showToast(type, title, message) {
    const toast = document.getElementById('toast');
    const container = document.getElementById('toast-container');
    const icon = document.getElementById('toast-icon');
    const titleEl = document.getElementById('toast-title');
    const msgEl = document.getElementById('toast-message');

    if (!toast || !container || !icon || !titleEl || !msgEl) {
        console.log(`[${type}] ${title}: ${message}`);
        return;
    }

    // Remove existing classes
    container.classList.remove('border-green-500', 'border-red-500', 'border-blue-500', 'border-yellow-500');
    icon.classList.remove('ri-checkbox-circle-fill', 'ri-error-warning-fill', 'ri-information-fill');
    icon.classList.remove('text-green-500', 'text-red-500', 'text-blue-500', 'text-yellow-500');

    // Set type-specific styles
    const typeStyles = {
        success: { border: 'border-green-500', icon: 'ri-checkbox-circle-fill', color: 'text-green-500' },
        error: { border: 'border-red-500', icon: 'ri-error-warning-fill', color: 'text-red-500' },
        info: { border: 'border-blue-500', icon: 'ri-information-fill', color: 'text-blue-500' },
        warning: { border: 'border-yellow-500', icon: 'ri-error-warning-fill', color: 'text-yellow-500' }
    };

    const style = typeStyles[type] || typeStyles.info;
    container.classList.add(style.border);
    icon.classList.add(style.icon, style.color);

    titleEl.textContent = title;
    msgEl.textContent = message;

    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.remove('translate-x-full'), 10);

    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.classList.add('hidden'), 300);
    }, 3000);
}

// ============================================
// INITIALIZATION
// ============================================

/**
 * Initialize shared functionality
 * Call this at the start of each page
 */
async function initShared() {
    await syncServerTime();
    // Re-sync every 30 seconds
    setInterval(syncServerTime, 30000);
}
