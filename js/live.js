// ============================================
// SOUND SYSTEM
// ============================================

let soundEnabled = false;
let lastVoteCount = 0;
let countdownWarningPlayed = false;
let audioContext = null;

function initSound() {
    // Check localStorage for user preference
    const savedPreference = localStorage.getItem('live_sound_enabled');

    if (savedPreference === null) {
        // First time - show activation overlay
        document.getElementById('sound-activation-overlay').classList.remove('hidden');
    } else {
        soundEnabled = savedPreference === 'true';
        updateSoundIcon();
    }

    // Initialize Web Audio Context (lazy initialization)
    if (soundEnabled && !audioContext) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }
}

function enableSound() {
    soundEnabled = true;
    localStorage.setItem('live_sound_enabled', 'true');
    document.getElementById('sound-activation-overlay').classList.add('hidden');
    updateSoundIcon();

    // Initialize audio context
    if (!audioContext) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }
}

function disableSound() {
    soundEnabled = false;
    localStorage.setItem('live_sound_enabled', 'false');
    document.getElementById('sound-activation-overlay').classList.add('hidden');
    updateSoundIcon();
}

function toggleSound() {
    if (soundEnabled) {
        disableSound();
    } else {
        enableSound();
    }
}

function updateSoundIcon() {
    const icon = document.getElementById('sound-icon');
    if (soundEnabled) {
        icon.className = 'ri-volume-up-line text-2xl text-white';
    } else {
        icon.className = 'ri-volume-mute-line text-2xl text-white';
    }
}

function playNewVoteSound() {
    if (!soundEnabled || !audioContext) return;

    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    // Pleasant "ding" sound
    oscillator.frequency.value = 800;
    oscillator.type = 'sine';

    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);

    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.3);
}

function playCountdownWarning() {
    if (!soundEnabled || !audioContext) return;

    // Triple beep warning
    for (let i = 0; i < 3; i++) {
        setTimeout(() => {
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            // Urgent beep sound
            oscillator.frequency.value = 1200;
            oscillator.type = 'square';

            gainNode.gain.setValueAtTime(0.2, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.15);
        }, i * 200);
    }
}

function playFinalCountdownBeep(secondsLeft) {
    if (!soundEnabled || !audioContext) return;

    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    // Beep sound - pitch increases as time runs out (more urgent)
    // 10s: 800Hz -> 1s: 1600Hz
    const baseFrequency = 800;
    const frequency = baseFrequency + (11 - secondsLeft) * 80;
    oscillator.frequency.value = frequency;
    oscillator.type = 'sine';

    // Volume also increases slightly towards the end
    const baseVolume = 0.25;
    const volume = baseVolume + (11 - secondsLeft) * 0.025;
    gainNode.gain.setValueAtTime(volume, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.2);
}

// ============================================
// DYNAMIC QR CODE
// ============================================

async function updateQRCode() {
    try {
        let votingUrl;

        // Try to get URL from server API
        try {
            const response = await fetch('api/server_info.php');
            const data = await response.json();
            votingUrl = data.voting_url;
        } catch (error) {
            // Fallback to client-side detection
            console.warn('Failed to fetch server info, using client-side detection:', error);
            votingUrl = `${window.location.protocol}//${window.location.host}/`;
        }

        // Generate QR code URL (200x200 for better quality)
        const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(votingUrl)}`;

        // Update QR code image
        const qrImg = document.getElementById('qr-code');
        if (qrImg) {
            qrImg.src = qrApiUrl;
            qrImg.alt = `QR Code: ${votingUrl}`;
        }

        // Update URL display
        const urlDisplay = document.getElementById('voting-url-display');
        if (urlDisplay) {
            urlDisplay.textContent = votingUrl;
        }

        console.log('QR Code updated:', votingUrl);
    } catch (error) {
        console.error('Failed to update QR code:', error);
    }
}

// ============================================
// VICTORY PODIUM & CELEBRATION
// ============================================

let confettiInterval = null;
let currentWinners = [];

/**
 * Trigger Victory Celebration: Open Podium Modal + Continuous Confetti
 */
function celebrateWinners() {
    if (typeof confetti === 'undefined') {
        console.warn('Confetti library not loaded');
        return;
    }

    // 1. Get current data for Podium
    populatePodiumData();

    // 2. Show Modal
    const modal = document.getElementById('victory-modal');
    modal.classList.remove('hidden');

    // 3. Trigger Layout Animations (Slide Up)
    setTimeout(() => {
        document.getElementById('podium-rank-1').classList.add('podium-active');
        document.getElementById('podium-rank-2').classList.add('podium-active');
        document.getElementById('podium-rank-3').classList.add('podium-active');
    }, 100);

    // 4. Start Continuous Confetti
    startContinuousConfetti();

    // 5. Play Victory Sound (Looping or Long)
    playVictorySound();
}

function populatePodiumData() {
    // Helper to safe update
    const updatePodiumSlot = (rank) => {
        const nameEl = document.getElementById(`rank-${rank}-name`);
        const scoreEl = document.getElementById(`rank-${rank}-score`);
        const imgEl = document.getElementById(`rank-${rank}-img`);

        const pName = document.getElementById(`podium-name-${rank}`);
        const pScore = document.getElementById(`podium-score-${rank}`);
        const pImg = document.getElementById(`podium-img-${rank}`);

        if (pName && nameEl) pName.textContent = nameEl.textContent;
        if (pScore && scoreEl) pScore.textContent = scoreEl.textContent;
        if (pImg && imgEl) pImg.src = imgEl.src;
    };

    updatePodiumSlot(1);
    updatePodiumSlot(2);
    updatePodiumSlot(3);
}

function startContinuousConfetti() {
    // Clear existing if any
    if (confettiInterval) clearInterval(confettiInterval);

    const duration = 1500; // Burst frequency
    const defaults = { startVelocity: 30, spread: 360, ticks: 100, zIndex: 200 };

    function randomInRange(min, max) {
        return Math.random() * (max - min) + min;
    }

    // Immediate Burst
    triggerConfettiBurst(defaults, randomInRange);

    // Loop Indefinitely
    confettiInterval = setInterval(() => {
        triggerConfettiBurst(defaults, randomInRange);
    }, duration);
}

function triggerConfettiBurst(defaults, randomFunc) {
    // Burst from left
    confetti({
        ...defaults,
        particleCount: 50,
        origin: { x: randomFunc(0.1, 0.3), y: Math.random() - 0.2 }
    });

    // Burst from right
    confetti({
        ...defaults,
        particleCount: 50,
        origin: { x: randomFunc(0.7, 0.9), y: Math.random() - 0.2 }
    });

    // Burst from center top
    confetti({
        ...defaults,
        particleCount: 80,
        origin: { x: 0.5, y: 0.3 },
        gravity: 1.2
    });
}

function stopCelebration() {
    // 1. Stop Confetti Loop
    if (confettiInterval) {
        clearInterval(confettiInterval);
        confettiInterval = null;
    }

    confetti.reset(); // Clear particles immediately

    // 2. Hide Modal
    const modal = document.getElementById('victory-modal');
    modal.classList.add('hidden');

    // 3. Reset Animations
    document.getElementById('podium-rank-1').classList.remove('podium-active');
    document.getElementById('podium-rank-2').classList.remove('podium-active');
    document.getElementById('podium-rank-3').classList.remove('podium-active');

    // 4. Stop Sound (Optional - depending on sound logic)
}

function playVictorySound() {
    if (!soundEnabled || !audioContext) return;

    // 3-note victory fanfare (Do-Mi-Sol) - Replayed every loop or just once?
    // For continuous celebration, let's play it once grandly
    const notes = [
        { freq: 523.25, delay: 0, type: 'triangle' },    // C5
        { freq: 659.25, delay: 0.2, type: 'triangle' },  // E5
        { freq: 783.99, delay: 0.4, type: 'triangle' },  // G5
        { freq: 1046.50, delay: 0.8, type: 'sine' }      // C6 (High C)
    ];

    notes.forEach(note => {
        setTimeout(() => {
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = note.freq;
            oscillator.type = note.type;

            gainNode.gain.setValueAtTime(0.4, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 1.5);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 1.5);
        }, note.delay * 1000);
    });
}

// Keyboard shortcut: Press 'C' to celebrate
document.addEventListener('keydown', (e) => {
    if (e.key === 'c' || e.key === 'C') {
        celebrateWinners();
    }
});

// ============================================
// CHART SETUP
// ============================================

const ctx = document.getElementById('racingChart').getContext('2d');

Chart.register(ChartDataLabels);

let chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [],
        datasets: [{
            label: 'Điểm số',
            data: [],
            backgroundColor: [],
            borderColor: [],
            borderWidth: 1,
            borderRadius: 8,
            barThickness: 40,
        }]
    },
    options: {
        indexAxis: 'y', // Horizontal bar
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: { right: 50 }
        },
        scales: {
            x: {
                beginAtZero: true,
                grid: { color: 'rgba(255, 255, 255, 0.1)' },
                ticks: { color: '#94a3b8' }
            },
            y: {
                grid: { display: false },
                ticks: {
                    color: 'white',
                    font: { size: 14, weight: 'bold', family: 'Outfit' }
                }
            }
        },
        plugins: {
            legend: { display: false },
            datalabels: {
                anchor: 'end',
                align: 'end',
                color: 'white',
                font: { weight: 'bold' },
                formatter: Math.round
            }
        },
        animation: {
            duration: 1000,
            easing: 'easeOutQuart'
        }
    }
});

let performersData = [];

// Session Management for Live Page
async function loadSession() {
    try {
        const session = await fetchSession();
        updateSessionUI(session);
    } catch (error) {
        console.error('Failed to load session:', error);
    }
}

function updateSessionUI(session) {
    const container = document.getElementById('session-timer-container');

    if (session.status === 'open' && session.end_time) {
        container.classList.remove('hidden');
        sessionEnded = false; // Session is still active

        // Start custom countdown with warning sound
        startLiveCountdown(session.end_time);
    } else {
        container.classList.add('hidden');
        stopCountdown();
        countdownWarningPlayed = false; // Reset warning flag

        // Session has ended (status is 'closed' or no end_time)
        sessionEnded = true;
        updateCelebrateButtonVisibility(); // Update button visibility
    }
}

// Custom countdown for live page with sound warning
let liveCountdownInterval = null;
let lastBeepSecond = -1; // Track which second we last beeped

function startLiveCountdown(endTime) {
    // Clear existing interval
    if (liveCountdownInterval) {
        clearInterval(liveCountdownInterval);
    }

    // Reset beep tracker
    lastBeepSecond = -1;

    // Update function with warning logic
    const updateWithWarning = () => {
        const remaining = calculateRemaining(endTime);

        // Update display
        const display = document.getElementById('live-countdown');
        if (display) {
            display.textContent = formatCountdown(remaining);
        }

        // Play warning at 30 seconds
        if (remaining <= 30000 && remaining > 29000 && !countdownWarningPlayed) {
            playCountdownWarning();
            countdownWarningPlayed = true;
        }

        // Play beep for each second in final 10 seconds
        if (remaining <= 10000 && remaining > 0) {
            const secondsLeft = Math.ceil(remaining / 1000);
            if (secondsLeft !== lastBeepSecond) {
                playFinalCountdownBeep(secondsLeft);
                lastBeepSecond = secondsLeft;
            }
        }

        // Reset warning flag when session restarts
        if (remaining > 30000) {
            countdownWarningPlayed = false;
            lastBeepSecond = -1;
        }

        // Reload session when expired
        if (remaining <= 0) {
            clearInterval(liveCountdownInterval);
            liveCountdownInterval = null;
            lastBeepSecond = -1;
            loadSession();
        }
    };

    // Initial update
    updateWithWarning();

    // Start interval
    liveCountdownInterval = setInterval(updateWithWarning, 1000);
}

// ============================================
// ADMIN DETECTION & CELEBRATE BUTTON CONTROL
// ============================================

let isAdmin = false;
let sessionEnded = false;

/**
 * Check if current user is Admin (via Session API)
 */
async function checkAdminStatus() {
    try {
        const response = await fetch('api/admin-status.php');
        const data = await response.json();
        isAdmin = data.is_admin === true;
        console.log('Admin status:', isAdmin, data.username ? `(${data.username})` : '');
        updateCelebrateButtonVisibility();
    } catch (error) {
        console.error('Failed to check admin status:', error);
        isAdmin = false;
        updateCelebrateButtonVisibility();
    }
}

/**
 * Update Celebrate Button visibility based on Admin status and Session status
 */
function updateCelebrateButtonVisibility() {
    const celebrateBtn = document.getElementById('celebrate-btn');
    if (!celebrateBtn) return;

    // Show button ONLY if:
    // 1. User is Admin
    // 2. Session has ended
    if (isAdmin && sessionEnded) {
        celebrateBtn.classList.remove('hidden');
    } else {
        celebrateBtn.classList.add('hidden');
    }
}

// Initialize shared utilities and start polling
(async function init() {
    await initShared();
    initSound(); // Initialize sound system
    await checkAdminStatus(); // Check admin status from API (async)
    updateData();
    loadSession();
    updateQRCode(); // Initialize QR code

    // Poll every 3 seconds
    setInterval(updateData, 3000);
    setInterval(loadSession, 5000);
})();


async function updateData() {
    try {
        const response = await fetch('api/results.php');
        const data = await response.json();

        // Update Total Votes UI
        if (data.total_votes !== undefined) {
            const currentVoteCount = data.total_votes;

            // Detect new votes and play sound
            if (lastVoteCount > 0 && currentVoteCount > lastVoteCount) {
                playNewVoteSound();
            }
            lastVoteCount = currentVoteCount;

            document.getElementById('total-votes').textContent = currentVoteCount;
        }

        const results = data.results || []; // Assumes sorted array from API

        // Update Leaderboard UI
        updateLeaderboard(results);

        // Update Chart
        const labels = results.map(p => p.name);
        const scores = results.map(p => p.score);
        const colors = results.map(p => p.color || '#6366f1');

        chart.data.labels = labels;
        chart.data.datasets[0].data = scores;
        chart.data.datasets[0].backgroundColor = colors;
        chart.update();

    } catch (error) {
        console.error('Error fetching results:', error);
    }
}

function updateLeaderboard(results) {
    if (results.length > 0) updateRankCard(1, results[0]);
    if (results.length > 1) updateRankCard(2, results[1]);
    if (results.length > 2) updateRankCard(3, results[2]);
}

function updateRankCard(rank, data) {
    const nameEl = document.getElementById(`rank-${rank}-name`);
    const scoreEl = document.getElementById(`rank-${rank}-score`);
    const imgEl = document.getElementById(`rank-${rank}-img`);

    if (nameEl) nameEl.textContent = data.name;
    if (scoreEl) scoreEl.textContent = `${data.score} pts`;
    if (imgEl && data.image) imgEl.src = data.image;
}
