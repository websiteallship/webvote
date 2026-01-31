/**
 * Device Fingerprinting Utility for ALLSHIP Voting System
 * Generates a unique identifier per device to prevent duplicate voting
 * Uses canvas, WebGL, screen info, timezone, and other browser characteristics
 */

// Cache fingerprint to avoid recalculation
let cachedFingerprint = null;

/**
 * Generate a unique device fingerprint
 * @returns {Promise<string>} SHA-256 hash representing this device
 */
async function generateFingerprint() {
    // Return cached value if available
    if (cachedFingerprint) {
        return cachedFingerprint;
    }

    // Check localStorage first (faster, persistent)
    const storedFP = localStorage.getItem('allship_device_fp');
    if (storedFP) {
        cachedFingerprint = storedFP;
        return storedFP;
    }

    try {
        const components = [];

        // 1. Canvas fingerprint (unique rendering per device)
        components.push(getCanvasFingerprint());

        // 2. WebGL fingerprint (GPU info)
        components.push(getWebGLFingerprint());

        // 3. Screen characteristics
        components.push(getScreenFingerprint());

        // 4. Timezone
        components.push(Intl.DateTimeFormat().resolvedOptions().timeZone || 'unknown');

        // 5. Language preferences
        components.push(navigator.language || 'unknown');
        components.push((navigator.languages || []).join(','));

        // 6. Hardware concurrency (CPU cores)
        components.push(String(navigator.hardwareConcurrency || 0));

        // 7. Device memory (if available)
        components.push(String(navigator.deviceMemory || 0));

        // 8. Platform
        components.push(navigator.platform || 'unknown');

        // 9. Touch support
        components.push(String('ontouchstart' in window));
        components.push(String(navigator.maxTouchPoints || 0));

        // 10. Color depth
        components.push(String(screen.colorDepth || 0));

        // Combine and hash
        const combined = components.join('|');
        const fingerprint = await sha256(combined);

        // Store in localStorage for persistence
        try {
            localStorage.setItem('allship_device_fp', fingerprint);
        } catch (e) {
            // localStorage might be disabled
            console.warn('Could not store fingerprint in localStorage');
        }

        cachedFingerprint = fingerprint;
        return fingerprint;
    } catch (error) {
        console.error('Error generating fingerprint:', error);
        // Fallback to User Agent-based fingerprint
        return await getFallbackFingerprint();
    }
}

/**
 * Canvas fingerprint - each device renders slightly differently
 */
function getCanvasFingerprint() {
    try {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        canvas.width = 200;
        canvas.height = 50;

        // Draw various shapes and text
        ctx.textBaseline = 'top';
        ctx.font = '14px Arial';
        ctx.fillStyle = '#f60';
        ctx.fillRect(0, 0, 100, 25);

        ctx.fillStyle = '#069';
        ctx.fillText('ALLSHIP Voting 🎵', 2, 15);

        ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
        ctx.fillText('Device FP', 4, 35);

        // Get data URL
        return canvas.toDataURL();
    } catch (e) {
        return 'canvas-not-supported';
    }
}

/**
 * WebGL fingerprint - GPU vendor and renderer
 */
function getWebGLFingerprint() {
    try {
        const canvas = document.createElement('canvas');
        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');

        if (!gl) return 'webgl-not-supported';

        const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');

        if (debugInfo) {
            const vendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
            const renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
            return `${vendor}~${renderer}`;
        }

        return 'webgl-no-debug-info';
    } catch (e) {
        return 'webgl-error';
    }
}

/**
 * Screen fingerprint
 */
function getScreenFingerprint() {
    return `${screen.width}x${screen.height}x${screen.availWidth}x${screen.availHeight}x${window.devicePixelRatio || 1}`;
}

/**
 * SHA-256 hash function using Web Crypto API
 */
async function sha256(message) {
    const encoder = new TextEncoder();
    const data = encoder.encode(message);
    const buffer = await crypto.subtle.digest('SHA-256', data);
    return Array.from(new Uint8Array(buffer))
        .map(b => b.toString(16).padStart(2, '0'))
        .join('');
}

/**
 * Fallback fingerprint - ALWAYS returns a valid value
 * Uses User Agent + screen info for browsers that block localStorage
 */
async function getFallbackFingerprint() {
    // Try to get from localStorage first
    try {
        let fallbackId = localStorage.getItem('allship_fallback_fp');
        if (fallbackId) {
            cachedFingerprint = fallbackId;
            return fallbackId;
        }
    } catch (e) {
        // localStorage blocked, continue to generate new one
    }

    // Generate fingerprint from available info (no localStorage dependency)
    const components = [];

    // User Agent is always available
    components.push(navigator.userAgent || 'unknown');

    // Screen info
    components.push(String(screen.width || 0));
    components.push(String(screen.height || 0));
    components.push(String(screen.colorDepth || 0));
    components.push(String(window.devicePixelRatio || 1));

    // Timezone
    try {
        components.push(Intl.DateTimeFormat().resolvedOptions().timeZone || 'unknown');
    } catch (e) {
        components.push('tz-unknown');
    }

    // Language
    components.push(navigator.language || 'unknown');

    // Hardware
    components.push(String(navigator.hardwareConcurrency || 0));

    // Platform
    components.push(navigator.platform || 'unknown');

    // Touch capability
    components.push(String('ontouchstart' in window));
    components.push(String(navigator.maxTouchPoints || 0));

    const combined = 'fb_' + components.join('|');

    // Try to hash it
    let fallbackId;
    try {
        fallbackId = await sha256(combined);
    } catch (e) {
        // Even if sha256 fails, create a deterministic ID
        fallbackId = 'raw_' + btoa(combined).substring(0, 32);
    }

    // Try to store for future use
    try {
        localStorage.setItem('allship_fallback_fp', fallbackId);
    } catch (e) {
        // Ignore if localStorage is blocked
    }

    cachedFingerprint = fallbackId;
    return fallbackId;
}

/**
 * Clear cached fingerprint (for testing)
 */
function clearFingerprintCache() {
    cachedFingerprint = null;
    localStorage.removeItem('allship_device_fp');
    localStorage.removeItem('allship_fallback_fp');
}
