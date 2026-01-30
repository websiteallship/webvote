document.addEventListener('DOMContentLoaded', async () => {
    // Check if already voted first
    const hasVoted = await checkVotedStatus();
    if (!hasVoted) {
        loadPerformers();
        setupModalEvents();
    }
    loadSession();
    // Check session every 5 seconds
    setInterval(loadSession, 5000);
});

// Check if already voted in current session
async function checkVotedStatus() {
    try {
        const res = await fetch('api/votes.php?check=1');
        const data = await res.json();
        
        if (data.voted && data.session_active) {
            showAlreadyVotedUI();
            return true;
        }
        return false;
    } catch (error) {
        console.error('Error checking vote status:', error);
        return false;
    }
}

// Show already voted UI
function showAlreadyVotedUI() {
    // Hide other session states
    document.getElementById('session-timer')?.classList.add('hidden');
    document.getElementById('session-closed')?.classList.add('hidden');
    document.getElementById('session-expired')?.classList.add('hidden');
    
    // Show already voted message
    document.getElementById('already-voted')?.classList.remove('hidden');
    
    // Hide voting area
    const mainContent = document.querySelector('.container.mx-auto.px-4.max-w-3xl');
    if (mainContent) mainContent.classList.add('hidden');
}

let performers = [];
let votes = {
    1: null,
    2: null,
    3: null
};

async function loadPerformers() {
    try {
        const response = await fetch('api/performers.php');
        const data = await response.json();
        performers = data;
        renderPerformers();
    } catch (error) {
        console.error('Error loading performers:', error);
        // Fallback or show error
        document.getElementById('performers-list').innerHTML = '<p class="text-center text-red-500 w-full col-span-2">Không thể tải danh sách tiết mục.</p>';
    }
}

function renderPerformers() {
    const container = document.getElementById('performers-list');
    container.innerHTML = '';

    performers.forEach(p => {
        const isSelected = Object.values(votes).includes(p.id);
        const rank = getRankByPerformerId(p.id);

        const card = document.createElement('div');
        card.className = `performer-card glass-card p-4 rounded-xl flex items-center gap-4 cursor-pointer border border-transparent hover:border-gray-200 ${isSelected ? 'selected ring-1 ring-primary/20' : ''}`;
        card.onclick = () => toggleSelection(p.id);

        card.innerHTML = `
            <div class="relative w-16 h-16 flex-shrink-0">
                <img src="${p.image}" class="w-full h-full object-cover rounded-lg shadow-sm" alt="${p.name}">
                ${rank ? `<div class="absolute -top-2 -right-2 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white shadow-sm rank-${rank}">${rank}</div>` : ''}
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-bold text-gray-800 truncate">${p.name}</h3>
                <p class="text-sm text-gray-500 truncate">${p.performer}</p>
            </div>
            <div class="w-8 h-8 rounded-full border-2 border-gray-200 flex items-center justify-center transition-colors ${isSelected ? 'bg-primary border-primary text-white' : 'text-transparent'}">
                <i class="ri-check-line"></i>
            </div>
        `;
        container.appendChild(card);
    });
}

function getRankByPerformerId(id) {
    for (let r in votes) {
        if (votes[r] === id) return r;
    }
    return null;
}

function toggleSelection(id) {
    const currentRank = getRankByPerformerId(id);

    if (currentRank) {
        // Deselect
        votes[currentRank] = null;
        updateSlotUI(currentRank, null);
    } else {
        // Select logic: Fill 1 -> 2 -> 3
        if (!votes[1]) updateVote(1, id);
        else if (!votes[2]) updateVote(2, id);
        else if (!votes[3]) updateVote(3, id);
        else {
            showToast('Bạn chỉ được chọn tối đa 3 tiết mục!');
            return; // Full
        }
    }
    renderPerformers();
    checkSubmitButton();
}

function updateVote(rank, id) {
    votes[rank] = id;
    const performer = performers.find(p => p.id === id);
    updateSlotUI(rank, performer);
}

function updateSlotUI(rank, performer) {
    const slot = document.getElementById(`slot-${rank}`);
    const nameEl = document.getElementById(`name-slot-${rank}`);
    const imgEl = document.getElementById(`img-slot-${rank}`);
    const iconEl = slot.querySelector('i.ri-add-line');
    const overlayEl = document.getElementById(`overlay-slot-${rank}`); // Close overlay
    
    if (performer) {
        // Filled state
        slot.classList.add('border-primary'); // Highlight border
        slot.classList.remove('border-dashed', 'border-gray-300');
        slot.classList.add('border-solid');
        
        nameEl.textContent = performer.name;
        nameEl.classList.add('text-primary', 'font-bold');
        
        imgEl.src = performer.image;
        imgEl.classList.remove('hidden');
        
        iconEl.classList.add('hidden');
        overlayEl.classList.remove('hidden');
        
        // Remove click handler from slot to avoid re-triggering selection if we handled it via list
        // create a remove handler
        overlayEl.onclick = (e) => {
            e.stopPropagation();
            votes[rank] = null;
            updateSlotUI(rank, null);
            renderPerformers();
            checkSubmitButton();
        };

    } else {
        // Empty state
        slot.classList.remove('border-primary', 'border-solid');
        slot.classList.add('border-dashed', 'border-gray-300');
        
        nameEl.textContent = 'Chưa chọn';
        nameEl.classList.remove('text-primary', 'font-bold');
        
        imgEl.src = '';
        imgEl.classList.add('hidden');
        
        iconEl.classList.remove('hidden');
        overlayEl.classList.add('hidden');
    }
}

function checkSubmitButton() {
    // Only allow submit if rank 1, 2, 3 are filled? Or just at least rank 1?
    // Rule: "Giải 1 2 3", implies need to pick 3.
    const isFull = votes[1] && votes[2] && votes[3];
    const btn = document.getElementById('submit-btn');
    btn.disabled = !isFull;
    if(isFull) {
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        btn.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

function openSubmitModal() {
    if (!votes[1] || !votes[2] || !votes[3]) return;
    document.getElementById('submit-modal').classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('modal-content').classList.remove('translate-y-full');
    }, 10);
}

function closeSubmitModal() {
    document.getElementById('modal-content').classList.add('translate-y-full');
    setTimeout(() => {
        document.getElementById('submit-modal').classList.add('hidden');
    }, 300);
}

function setupModalEvents() {
    document.getElementById('voter-name').addEventListener('input', () => {
        document.getElementById('name-error').classList.add('hidden');
    });
}

async function submitVote() {
    const nameInput = document.getElementById('voter-name');
    const name = nameInput.value.trim();
    
    if (!name) {
        document.getElementById('name-error').classList.remove('hidden');
        nameInput.focus();
        return;
    }

    const payload = {
        voter: name,
        votes: {
            rank1: votes[1],
            rank2: votes[2],
            rank3: votes[3]
        },
        timestamp: new Date().toISOString()
    };

    try {
        const res = await fetch('api/votes.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        const result = await res.json();
        
        if (result.success) {
            closeSubmitModal();
            document.getElementById('success-modal').classList.remove('hidden');
            // Prevent further voting? Maybe clear localStorage logic if needed.
        } else {
            showToast(result.message || 'Có lỗi xảy ra!');
        }
    } catch (err) {
        showToast('Lỗi kết nối server!');
        console.error(err);
    }
}

function showToast(msg) {
    const toast = document.getElementById('toast');
    document.getElementById('toast-message').textContent = msg;
    
    // Show toast
    toast.classList.remove('hidden');
    
    // Slide in animation
    setTimeout(() => {
        toast.classList.add('translate-x-0');
        toast.classList.remove('translate-x-full');
    }, 10);
    
    // Hide after 3 seconds
    setTimeout(() => {
        toast.classList.remove('translate-x-0');
        toast.classList.add('translate-x-full');
        
        // Remove from DOM after animation
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 300);
    }, 3000);
}

// Session Management
let sessionCountdownInterval = null;

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
    const timerEl = document.getElementById('session-timer');
    const closedEl = document.getElementById('session-closed');
    const expiredEl = document.getElementById('session-expired');
    const submitBtn = document.getElementById('submit-btn');
    const performerCards = document.querySelectorAll('.performer-card');

    // Hide all status elements first
    timerEl.classList.add('hidden');
    closedEl.classList.add('hidden');
    expiredEl.classList.add('hidden');

    if (session.status === 'open') {
        // Show countdown timer
        timerEl.classList.remove('hidden');
        
        // Enable voting
        submitBtn.disabled = false;
        performerCards.forEach(card => {
            card.style.pointerEvents = 'auto';
            card.style.opacity = '1';
        });
        
        // Start countdown
        if (sessionCountdownInterval) clearInterval(sessionCountdownInterval);
        sessionCountdownInterval = setInterval(() => updateSessionCountdown(session.end_time), 1000);
        updateSessionCountdown(session.end_time);
    } else if (session.status === 'expired') {
        // Show expired message
        expiredEl.classList.remove('hidden');
        
        // Disable voting
        submitBtn.disabled = true;
        performerCards.forEach(card => {
            card.style.pointerEvents = 'none';
            card.style.opacity = '0.6';
        });
        
        if (sessionCountdownInterval) clearInterval(sessionCountdownInterval);
    } else {
        // Show closed message
        closedEl.classList.remove('hidden');
        
        // Disable voting but allow viewing
        submitBtn.disabled = true;
        performerCards.forEach(card => {
            card.style.pointerEvents = 'none';
            card.style.opacity = '0.6';
        });
        
        if (sessionCountdownInterval) clearInterval(sessionCountdownInterval);
    }
}

function updateSessionCountdown(endTime) {
    const now = new Date().getTime();
    // Parse the datetime string correctly (PHP format: Y-m-d H:i:s)
    // Replace space with T to make it ISO-like, then parse as local time
    const endTimeStr = endTime.replace(' ', 'T');
    const end = new Date(endTimeStr).getTime();
    const remaining = Math.max(0, end - now);

    const minutes = Math.floor(remaining / 60000);
    const seconds = Math.floor((remaining % 60000) / 1000);

    const display = document.getElementById('countdown-timer');
    if (display) {
        display.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }

    if (remaining <= 0) {
        clearInterval(sessionCountdownInterval);
        loadSession(); // Reload to show expired state
    }
}
