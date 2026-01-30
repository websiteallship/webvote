/**
 * Admin Panel JavaScript for ALLSHIP GALA DINNER Voting System
 * Extracted from inline script in admin.html
 */

// ============================================
// GLOBAL STATE
// ============================================
let performers = [];
let votes = [];
let sessionInterval = null;

// ============================================
// INITIALIZATION
// ============================================

document.addEventListener('DOMContentLoaded', async () => {
    // Initialize shared utilities
    await initShared();

    // Check auth first, then load data
    await checkAuth();
    loadData();
    loadSession();

    // Auto-refresh: Reload votes every 10 seconds (admin only, 1-2 users)
    setInterval(loadData, 10000);

    // Refresh session every 30 seconds
    setInterval(loadSession, 30000);
});

// ============================================
// AUTHENTICATION
// ============================================

async function checkAuth() {
    try {
        const response = await fetch('api/login.php');
        const result = await response.json();

        if (!result.logged_in) {
            window.location.href = 'login.html';
        }
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = 'login.html';
    }
}

async function logout() {
    await fetch('api/login.php', { method: 'DELETE' });
    window.location.href = 'login.html';
}

// ============================================
// DATA LOADING
// ============================================

async function loadData() {
    const [perfRes, voteRes] = await Promise.all([
        fetch('api/performers.php'),
        fetch('api/votes.php')
    ]);
    performers = await perfRes.json();
    votes = await voteRes.json();

    renderTable();
    renderVotes();
    updateStats();
}

function updateStats() {
    document.getElementById('total-performers').textContent = performers.length;
    document.getElementById('total-votes-stat').textContent = votes.length;
}

// ============================================
// TAB MANAGEMENT
// ============================================

function showTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-link').forEach(el => {
        el.classList.remove('text-purple-600', 'bg-purple-50');
        el.classList.add('text-gray-600');
    });

    // Show selected tab
    document.getElementById('content-' + tab).classList.remove('hidden');
    document.getElementById('tab-' + tab).classList.add('text-purple-600', 'bg-purple-50');
    document.getElementById('tab-' + tab).classList.remove('text-gray-600');
}

// ============================================
// VOTES RENDERING
// ============================================

function renderVotes() {
    const tbody = document.getElementById('votes-table');
    const reversedVotes = [...votes].reverse();

    document.getElementById('vote-count').textContent = votes.length;

    // Clear existing rows
    tbody.innerHTML = '';

    reversedVotes.forEach(v => {
        const p1 = performers.find(p => p.id == v.votes.rank1)?.name || '---';
        const p2 = performers.find(p => p.id == v.votes.rank2)?.name || '---';
        const p3 = performers.find(p => p.id == v.votes.rank3)?.name || '---';
        const date = new Date(v.timestamp).toLocaleString('vi-VN');
        const ip = v.ip_address || 'N/A';
        const device = v.device || 'N/A';

        // Create row safely (XSS protected)
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';

        // Date cell
        const tdDate = document.createElement('td');
        tdDate.className = 'px-4 py-3 text-sm text-gray-500';
        tdDate.textContent = date;
        tr.appendChild(tdDate);

        // Voter name cell (XSS protected)
        const tdVoter = document.createElement('td');
        tdVoter.className = 'px-4 py-3 text-sm font-bold text-gray-800';
        tdVoter.textContent = v.voter;
        tr.appendChild(tdVoter);

        // Rank 1 cell
        const tdRank1 = document.createElement('td');
        tdRank1.className = 'px-4 py-3 text-sm text-yellow-600 font-medium';
        tdRank1.textContent = p1;
        tr.appendChild(tdRank1);

        // Rank 2 cell
        const tdRank2 = document.createElement('td');
        tdRank2.className = 'px-4 py-3 text-sm text-gray-600';
        tdRank2.textContent = p2;
        tr.appendChild(tdRank2);

        // Rank 3 cell
        const tdRank3 = document.createElement('td');
        tdRank3.className = 'px-4 py-3 text-sm text-orange-800';
        tdRank3.textContent = p3;
        tr.appendChild(tdRank3);

        // IP cell
        const tdIp = document.createElement('td');
        tdIp.className = 'px-4 py-3 text-sm text-gray-500 font-mono';
        tdIp.textContent = ip;
        tr.appendChild(tdIp);

        // Device cell
        const tdDevice = document.createElement('td');
        tdDevice.className = 'px-4 py-3 text-sm text-gray-600';
        tdDevice.textContent = device;
        tr.appendChild(tdDevice);

        tbody.appendChild(tr);
    });
}

// ============================================
// PERFORMERS TABLE RENDERING
// ============================================

function renderTable() {
    const tbody = document.getElementById('performers-table');
    tbody.innerHTML = '';

    performers.forEach(p => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';

        // ID cell
        const tdId = document.createElement('td');
        tdId.className = 'px-4 py-3 text-sm text-gray-500';
        tdId.textContent = p.id;
        tr.appendChild(tdId);

        // Image cell
        const tdImg = document.createElement('td');
        tdImg.className = 'px-4 py-3';
        const img = document.createElement('img');
        img.src = p.image; // Already sanitized on server
        img.className = 'w-12 h-12 rounded-lg object-cover border';
        img.alt = p.name;
        tdImg.appendChild(img);
        tr.appendChild(tdImg);

        // Name cell (XSS protected)
        const tdName = document.createElement('td');
        tdName.className = 'px-4 py-3 text-sm font-bold text-gray-800';
        tdName.textContent = p.name;
        tr.appendChild(tdName);

        // Performer cell (XSS protected)
        const tdPerformer = document.createElement('td');
        tdPerformer.className = 'px-4 py-3 text-sm text-gray-600';
        tdPerformer.textContent = p.performer;
        tr.appendChild(tdPerformer);

        // Color cell
        const tdColor = document.createElement('td');
        tdColor.className = 'px-4 py-3';
        const colorDiv = document.createElement('div');
        colorDiv.className = 'w-8 h-8 rounded-full border-2 border-gray-200';
        colorDiv.style.background = p.color;
        tdColor.appendChild(colorDiv);
        tr.appendChild(tdColor);

        // Actions cell
        const tdActions = document.createElement('td');
        tdActions.className = 'px-4 py-3 text-right';

        const btnEdit = document.createElement('button');
        btnEdit.className = 'text-blue-600 hover:text-blue-700 mr-3';
        btnEdit.onclick = () => editPerformer(p);
        btnEdit.innerHTML = '<i class="ri-edit-line text-lg"></i>';
        tdActions.appendChild(btnEdit);

        const btnDelete = document.createElement('button');
        btnDelete.className = 'text-red-600 hover:text-red-700';
        btnDelete.onclick = () => deletePerformer(p.id);
        btnDelete.innerHTML = '<i class="ri-delete-bin-line text-lg"></i>';
        tdActions.appendChild(btnDelete);

        tr.appendChild(tdActions);
        tbody.appendChild(tr);
    });
}

// ============================================
// PERFORMER CRUD OPERATIONS
// ============================================

async function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('image', file);

    try {
        const response = await fetch('api/upload.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            document.getElementById('p-image').value = result.url;
            document.getElementById('preview-img').src = result.url;
            document.getElementById('upload-preview').classList.remove('hidden');
        } else {
            alert('Lỗi upload: ' + result.message);
        }
    } catch (error) {
        alert('Lỗi kết nối server!');
    }
}

async function savePerformer(e) {
    e.preventDefault();
    const data = {
        id: document.getElementById('p-id').value || undefined,
        name: document.getElementById('p-name').value,
        performer: document.getElementById('p-performer').value,
        image: document.getElementById('p-image').value,
        color: document.getElementById('p-color').value
    };

    try {
        await fetch('api/performers.php', {
            method: 'POST',
            body: JSON.stringify(data)
        });
        resetForm();
        await loadData();
        showToast('success', 'Thành công', data.id ? 'Đã cập nhật tiết mục!' : 'Đã thêm tiết mục mới!');
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể lưu tiết mục!');
    }
}

function editPerformer(p) {
    document.getElementById('p-id').value = p.id;
    document.getElementById('p-name').value = p.name;
    document.getElementById('p-performer').value = p.performer;
    document.getElementById('p-image').value = p.image;
    document.getElementById('p-color').value = p.color;
    document.getElementById('form-title').textContent = 'Sửa Tiết Mục';

    // Show preview
    document.getElementById('preview-img').src = p.image;
    document.getElementById('upload-preview').classList.remove('hidden');

    // Scroll to form
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function deletePerformer(id) {
    if (!confirm('⚠️ Xác nhận xóa tiết mục?\n\nBạn có chắc chắn muốn xóa tiết mục này không?')) return;

    try {
        await fetch(`api/performers.php?id=${id}`, { method: 'DELETE' });
        await loadData();
        showToast('success', 'Đã xóa', 'Tiết mục đã được xóa thành công!');
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể xóa tiết mục!');
    }
}

async function resetVotes() {
    if (!confirm('🚨 CẢNH BÁO: XÓA TẤT CẢ PHIẾU BẦU\n\nHành động này sẽ xóa TOÀN BỘ dữ liệu phiếu bầu và KHÔNG THỂ KHÔI PHỤC!\n\nBạn có chắc chắn muốn tiếp tục?')) return;

    try {
        await fetch('api/votes.php', { method: 'DELETE' });
        await loadData();
        showToast('success', 'Đã xóa', 'Tất cả phiếu bầu đã được xóa!');
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể xóa phiếu bầu!');
    }
}

function resetForm() {
    document.getElementById('performer-form').reset();
    document.getElementById('p-id').value = '';
    document.getElementById('form-title').textContent = 'Thêm Tiết Mục Mới';
    document.getElementById('upload-preview').classList.add('hidden');
}

// ============================================
// SESSION MANAGEMENT
// ============================================

async function loadSession() {
    try {
        const session = await fetchSession();
        updateSessionUI(session);
    } catch (error) {
        console.error('Failed to load session:', error);
    }
}

function updateSessionUI(session) {
    const statusText = document.getElementById('session-status-text');
    const btnOpen = document.getElementById('btn-open-session');
    const btnClose = document.getElementById('btn-close-session');
    const countdownEl = document.getElementById('session-countdown');
    const durationSelector = document.getElementById('duration-selector');

    if (session.status === 'open') {
        statusText.textContent = `Đang mở - Kết thúc: ${new Date(session.end_time.replace(' ', 'T')).toLocaleTimeString('vi-VN')}`;
        btnOpen.classList.add('hidden');
        btnClose.classList.remove('hidden');
        countdownEl.classList.remove('hidden');
        durationSelector.classList.add('hidden');

        // Start countdown using shared function
        startCountdown(session.end_time, 'countdown-display', loadSession);
    } else if (session.status === 'expired') {
        statusText.textContent = 'Đã hết thời gian';
        btnOpen.classList.remove('hidden');
        btnClose.classList.add('hidden');
        countdownEl.classList.add('hidden');
        durationSelector.classList.remove('hidden');
        stopCountdown();
    } else {
        statusText.textContent = 'Chưa mở phiên';
        btnOpen.classList.remove('hidden');
        btnClose.classList.add('hidden');
        countdownEl.classList.add('hidden');
        durationSelector.classList.remove('hidden');
        stopCountdown();
    }
}

async function openSession() {
    const duration = document.getElementById('session-duration').value;

    if (!confirm(`🎯 Mở phiên bình chọn ${duration} phút?\n\nNgười dùng sẽ có thể bình chọn ngay sau khi mở phiên.`)) return;

    try {
        const response = await fetch('api/session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'open',
                duration: parseInt(duration)
            })
        });

        const result = await response.json();
        if (result.success) {
            await loadSession();
            showToast('success', 'Đã mở phiên', `Phiên bình chọn ${duration} phút đã được mở!`);
        } else {
            showToast('error', 'Lỗi', 'Không thể mở phiên bình chọn!');
        }
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể kết nối đến server!');
    }
}

async function closeSession() {
    if (!confirm('⏹️ Đóng phiên bình chọn?\n\nNgười dùng sẽ không thể bình chọn sau khi đóng phiên.')) return;

    try {
        const response = await fetch('api/session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'close' })
        });

        const result = await response.json();
        if (result.success) {
            await loadSession();
            showToast('info', 'Đã đóng phiên', 'Phiên bình chọn đã được đóng!');
        } else {
            showToast('error', 'Lỗi', 'Không thể đóng phiên bình chọn!');
        }
    } catch (error) {
        showToast('error', 'Lỗi', 'Không thể kết nối đến server!');
    }
}

// ============================================
// MOBILE MENU
// ============================================

function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
}

// ============================================
// EXPORT FUNCTIONALITY
// ============================================

async function exportResults(format) {
    if (!['csv', 'pdf'].includes(format)) {
        showToast('error', 'Lỗi', 'Định dạng không hợp lệ!');
        return;
    }

    try {
        // Show loading toast
        showToast('info', 'Đang xuất...', `Đang tạo file ${format.toUpperCase()}...`);

        // Call export API
        const response = await fetch(`api/export.php?format=${format}`);

        if (!response.ok) {
            throw new Error('Export failed');
        }

        // Get the blob
        const blob = await response.blob();

        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `results_${new Date().toISOString().slice(0, 10).replace(/-/g, '')}_${new Date().toTimeString().slice(0, 8).replace(/:/g, '')}.${format}`;
        document.body.appendChild(a);
        a.click();

        // Cleanup
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        // Show success toast
        showToast('success', 'Thành công', `Đã xuất file ${format.toUpperCase()}!`);
    } catch (error) {
        console.error('Export error:', error);
        showToast('error', 'Lỗi', 'Không thể xuất kết quả. Vui lòng thử lại!');
    }
}
