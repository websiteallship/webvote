<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YEP Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" href="images/logo.png">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Be Vietnam Pro', 'sans-serif'] }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50">
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-8">
                    <a href="index.html" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                        <img src="images/logo.png" alt="Allship Logo" class="h-8">
                        <h1 class="text-xl font-bold text-gray-800">Admin Panel</h1>
                    </a>
                    <div class="hidden md:flex gap-1">
                        <a href="#performers" onclick="showTab('performers')" id="tab-performers"
                            class="tab-link px-4 py-2 rounded-lg text-sm font-medium text-purple-600 bg-purple-50">
                            <i class="ri-music-2-line"></i> Tiết mục
                        </a>
                        <a href="#votes" onclick="showTab('votes')" id="tab-votes"
                            class="tab-link px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
                            <i class="ri-file-list-3-line"></i> Phiếu bầu
                        </a>
                        <a href="live.html" target="_blank"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
                            <i class="ri-live-line"></i> Xem Live
                        </a>
                        <a href="qr.html" target="_blank"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
                            <i class="ri-qr-code-line"></i> QR Code
                        </a>
                    </div>
                </div>
                <div class="flex gap-2 items-center">
                    <button onclick="logout()"
                        class="hidden md:block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="ri-logout-box-line"></i> Đăng xuất
                    </button>
                    <!-- Mobile Menu Button -->
                    <button onclick="toggleMobileMenu()"
                        class="md:hidden p-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i id="mobile-menu-icon" class="ri-menu-line text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Drawer -->
    <div id="mobile-menu" class="hidden md:hidden fixed inset-0 bg-black/50 z-50" onclick="toggleMobileMenu()">
        <div class="absolute right-0 top-0 h-full w-64 bg-white shadow-xl" onclick="event.stopPropagation()">
            <div class="p-4 border-b flex justify-between items-center">
                <h2 class="font-bold text-gray-800">Menu</h2>
                <button onclick="toggleMobileMenu()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <i class="ri-close-line text-xl"></i>
                </button>
            </div>
            <div class="flex flex-col p-4 gap-2">
                <a href="#performers" onclick="showTab('performers'); toggleMobileMenu()"
                    class="px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-600 flex items-center gap-2">
                    <i class="ri-music-2-line"></i> Tiết mục
                </a>
                <a href="#votes" onclick="showTab('votes'); toggleMobileMenu()"
                    class="px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-600 flex items-center gap-2">
                    <i class="ri-file-list-3-line"></i> Phiếu bầu
                </a>
                <a href="live.html" target="_blank"
                    class="px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-600 flex items-center gap-2">
                    <i class="ri-live-line"></i> Xem Live
                </a>
                <a href="qr.html" target="_blank"
                    class="px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-purple-50 hover:text-purple-600 flex items-center gap-2">
                    <i class="ri-qr-code-line"></i> QR Code
                </a>
                <hr class="my-2">
                <button onclick="logout()"
                    class="px-4 py-3 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 flex items-center gap-2 text-left w-full">
                    <i class="ri-logout-box-line"></i> Đăng xuất
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto p-6">
        <!-- Performers Tab -->
        <div id="content-performers" class="tab-content">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white p-4 rounded-xl shadow-sm border">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Tổng tiết mục</p>
                            <p class="text-2xl font-bold text-gray-800" id="total-performers">0</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="ri-music-2-fill text-purple-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div onclick="showTab('votes')"
                    class="bg-white p-4 rounded-xl shadow-sm border cursor-pointer hover:shadow-md hover:border-blue-300 transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Tổng phiếu bầu</p>
                            <p class="text-2xl font-bold text-gray-800" id="total-votes-stat">0</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="ri-file-list-3-fill text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-sm border">
                    <div class="flex items-center justify-between">
                        <div>
                            <button onclick="resetVotes()" class="text-sm text-red-600 hover:text-red-700 font-medium">
                                <i class="ri-delete-bin-line"></i> Xóa tất cả phiếu
                            </button>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="ri-delete-bin-fill text-red-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Control Panel -->
            <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-6 rounded-xl shadow-lg mb-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold mb-1 flex items-center gap-2">
                            <i class="ri-timer-line text-2xl"></i>
                            Phiên Bình Chọn
                        </h3>
                        <p class="text-sm opacity-90" id="session-status-text">Chưa mở phiên</p>
                    </div>
                    <div class="flex gap-3 items-center">
                        <!-- Duration selector -->
                        <div id="duration-selector" class="flex items-center gap-2 bg-white/20 rounded-lg px-4 py-2">
                            <label class="text-sm font-medium">Thời gian:</label>
                            <select id="session-duration"
                                class="bg-white/30 rounded px-3 py-1 text-sm font-medium outline-none">
                                <option value="3">3 phút</option>
                                <option value="5" selected>5 phút</option>
                                <option value="10">10 phút</option>
                                <option value="15">15 phút</option>
                                <option value="30">30 phút</option>
                            </select>
                        </div>
                        <!-- Control buttons -->
                        <button id="btn-open-session" onclick="openSession()"
                            class="bg-white text-purple-600 font-bold px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors hidden">
                            <i class="ri-play-fill"></i> Mở Phiên
                        </button>
                        <button id="btn-close-session" onclick="closeSession()"
                            class="bg-red-500 text-white font-bold px-6 py-2 rounded-lg hover:bg-red-600 transition-colors hidden">
                            <i class="ri-stop-fill"></i> Đóng Phiên
                        </button>
                        <button onclick="clearGlobalCache()"
                            class="bg-orange-500 text-white font-bold px-6 py-2 rounded-lg hover:bg-orange-600 transition-colors">
                            <i class="ri-delete-bin-line"></i> Clear Cache
                        </button>
                    </div>
                </div>
                <!-- Countdown display -->
                <div id="session-countdown" class="mt-4 text-center hidden">
                    <div class="text-4xl font-bold" id="countdown-display">05:00</div>
                    <div class="text-sm opacity-90 mt-1">Còn lại</div>
                </div>
            </div>

            <!-- Add/Edit Form -->
            <div class="bg-white p-6 rounded-xl shadow-sm border mb-6">
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <i class="ri-add-circle-line text-purple-600"></i>
                    <span id="form-title">Thêm Tiết Mục Mới</span>
                </h2>
                <form id="performer-form" onsubmit="savePerformer(event)" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" id="p-id">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tên tiết mục</label>
                        <input type="text" id="p-name" placeholder="Vũ điệu Marketing"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-200 focus:border-purple-500 outline-none"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Người biểu diễn</label>
                        <input type="text" id="p-performer" placeholder="Marketing Team"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-200 focus:border-purple-500 outline-none"
                            required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hình ảnh</label>
                        <div class="flex gap-2">
                            <input type="text" id="p-image" placeholder="URL hoặc upload file"
                                class="flex-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-200 focus:border-purple-500 outline-none"
                                required>
                            <label
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg cursor-pointer flex items-center gap-2 text-sm font-medium text-gray-700 border">
                                <i class="ri-upload-2-line"></i> Chọn file
                                <input type="file" id="image-upload" accept="image/*" class="hidden"
                                    onchange="handleImageUpload(event)">
                            </label>
                        </div>
                        <div id="upload-preview" class="mt-2 hidden">
                            <img id="preview-img" src="" class="w-32 h-32 object-cover rounded-lg border">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Màu đại diện</label>
                        <input type="color" id="p-color" class="h-10 w-full rounded-lg cursor-pointer" value="#6366f1">
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                            <i class="ri-save-line"></i> Lưu
                        </button>
                        <button type="button" onclick="resetForm()"
                            class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                            Hủy
                        </button>
                    </div>
                </form>
            </div>

            <!-- Performers List -->
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="p-4 border-b bg-gray-50">
                    <h2 class="text-lg font-bold">Danh sách tiết mục</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hình</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tên</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Người biểu
                                    diễn</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Màu</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Hành động
                                </th>
                            </tr>
                        </thead>
                        <tbody id="performers-table" class="divide-y">
                            <!-- JS renders here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Votes Tab -->
        <div id="content-votes" class="tab-content hidden">
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
                    <h2 class="text-lg font-bold">Danh sách phiếu bầu (<span id="vote-count">0</span>)</h2>
                    <div class="flex gap-2">
                        <button onclick="exportResults('csv')"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                            <i class="ri-file-excel-2-line"></i> Xuất CSV
                        </button>
                        <button onclick="exportResults('pdf')"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                            <i class="ri-file-pdf-line"></i> Xuất PDF
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto max-h-[600px]">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thời gian
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Người bầu
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-yellow-600 uppercase">Hạng 1
                                    (3đ)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hạng 2 (2đ)
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-orange-700 uppercase">Hạng 3
                                    (1đ)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Địa chỉ IP
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thiết bị
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Browser
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fingerprint
                                </th>
                            </tr>
                        </thead>
                        <tbody id="votes-table" class="divide-y">
                            <!-- JS renders here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-4 right-4 z-[100] hidden translate-x-full transition-transform duration-300">
        <div class="bg-white shadow-2xl rounded-xl p-4 flex items-center gap-3 border-l-4 min-w-[300px]"
            id="toast-container">
            <i id="toast-icon" class="text-2xl"></i>
            <div class="flex-1">
                <p class="text-sm font-bold text-gray-800" id="toast-title">Thông báo</p>
                <p class="text-xs text-gray-600" id="toast-message"></p>
            </div>
        </div>
    </div>

    <!-- Load shared utilities first -->
    <?php $gv = @file_get_contents('data/version.txt') ?: '1'; ?>
    <script src="js/shared.js?v=<?php echo filemtime('js/shared.js'); ?>&gv=<?php echo $gv; ?>"></script>
    <!-- Load admin-specific JavaScript -->
    <script src="js/admin.js?v=<?php echo filemtime('js/admin.js'); ?>&gv=<?php echo $gv; ?>"></script>
</body>

</html>