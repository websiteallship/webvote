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
let sessionInterval = null;

// Session Management for Live Page
async function loadSession() {
    try {
        const response = await fetch('api/session.php');
        const session = await response.json();
        updateSessionUI(session);
    } catch (error) {
        console.error('Failed to load session:', error);
    }
}

function updateSessionUI(session) {
    const container = document.getElementById('session-timer-container');
    const countdown = document.getElementById('live-countdown');
    
    if (session.status === 'open' && session.end_time) {
        container.classList.remove('hidden');
        
        // Start countdown
        if (sessionInterval) clearInterval(sessionInterval);
        sessionInterval = setInterval(() => updateCountdown(session.end_time), 1000);
        updateCountdown(session.end_time);
    } else {
        container.classList.add('hidden');
        if (sessionInterval) clearInterval(sessionInterval);
    }
}

function updateCountdown(endTime) {
    const now = new Date().getTime();
    const endTimeStr = endTime.replace(' ', 'T');
    const end = new Date(endTimeStr).getTime();
    const remaining = Math.max(0, end - now);

    const minutes = Math.floor(remaining / 60000);
    const seconds = Math.floor((remaining % 60000) / 1000);

    const display = document.getElementById('live-countdown');
    if (display) {
        display.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }

    if (remaining <= 0) {
        clearInterval(sessionInterval);
        loadSession(); // Reload to check for new session
    }
}

// Poll every 3 seconds
setInterval(updateData, 3000);
setInterval(loadSession, 5000); // Check session every 5 seconds
updateData();
loadSession(); // Initial load

async function updateData() {
    try {
        const response = await fetch('api/results.php');
        const data = await response.json();
        
        // Update Total Votes UI
        if (data.total_votes !== undefined) {
             document.getElementById('total-votes').textContent = data.total_votes;
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
