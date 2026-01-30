# ALLSHIP GALA DINNER - Website Bình Chọn Tiết Mục v1.0.0

Dự án website bình chọn trực tuyến cho sự kiện ALLSHIP GALA DINNER, hỗ trợ tính năng **Ranked Choice Voting** (bình chọn theo thứ hạng) và hiển thị kết quả **Real-time Racing Bar Chart**.

## ✨ Tính Năng Nổi Bật

### 🗳️ Bình Chọn
- **Ranked Choice Voting**: Người dùng chọn 3 tiết mục yêu thích nhất theo thứ tự:
    - 🥇 Hạng 1: **3 điểm**
    - 🥈 Hạng 2: **2 điểm**
    - 🥉 Hạng 3: **1 điểm**
- **Kiểm Soát Gian Lận**:
    - **Device Fingerprinting**: Nhận diện thiết bị duy nhất (Canvas, WebGL, Screen...) để ngăn chặn vote trùng lặp ngay cả khi đổi IP.
    - Chặn trùng tên người bình chọn.
    - Chặn thao tác khi phiên chưa mở hoặc đã kết thúc.
    - Fallback cơ chế IP + User Agent nếu thiết bị chặn fingerprint.

### 📺 Màn Hình Live (Projector)
- **Real-time Racing Bar Chart**: Biểu đồ đua cập nhật liên tục mỗi 3 giây
- **Dynamic QR Code**: Tự động detect IP server và hiển thị QR cho khán giả quét
- **🎉 Confetti Animation**: Hiệu ứng pháo giấy khi công bố kết quả (Nút "Celebrate!" hoặc phím 'C')
- **🔊 Sound Effects**: Âm thanh khi có vote mới, countdown warning (10 giây, hết giờ)

### ⏱️ Quản Lý Phiên
- Mở/đóng phiên bình chọn với thời gian tùy chọn (3, 5, 10, 15, 30 phút)
- Đồng bộ hóa đồng hồ giữa server và client
- Đếm ngược realtime trên cả trang Vote và Live

### 📊 Admin Panel
- Thêm/Sửa/Xóa tiết mục (hỗ trợ upload ảnh, chọn màu đại diện)
- Xem danh sách chi tiết từng phiếu bầu:
    - Thời gian, Người bầu, Hạng mục
    - Địa chỉ IP
    - Thiết bị (Mobile/Desktop)
    - **Trình duyệt (Browser)** (Chrome, Safari, etc.)
- **📥 Export kết quả**: Xuất CSV và PDF
- Trang QR Code riêng (`qr.html`) để chiếu lên màn hình

### 🚀 Hiệu Năng
- **Traffic Separation**: Tối ưu polling cho server yếu (1 vCPU, 1GB RAM)
    - Admin/Live: 3-10s (1-2 máy BTC)
    - Voter: 60s (100+ người dùng)
- Responsive Design: Mobile (voter) + Desktop/Projector (live/admin)

## 🏗️ Cài Đặt & Chạy

### Yêu cầu hệ thống
- **PHP**: Phiên bản 7.4 trở lên
- **Web Server**: Apache/Nginx hoặc PHP built-in server
- **Quyền ghi**: Thư mục `data/` và `uploads/`

### Hướng dẫn chạy nhanh
```bash
# Di chuyển vào thư mục dự án
cd webvote

# Khởi động PHP Server (chạy trên tất cả interface để mobile truy cập được)
php -S 0.0.0.0:8000
```

### Truy cập
| Trang | URL | Mô tả |
|-------|-----|-------|
| 📱 **Bình chọn** | `http://<IP>:8000/` | Trang cho khán giả vote |
| 📺 **Live Results** | `http://<IP>:8000/live.html` | Màn hình LED/Projector |
| 📷 **QR Code** | `http://<IP>:8000/qr.html` | Trang QR cho projector |
| ⚙️ **Admin** | `http://<IP>:8000/admin.html` | Quản trị viên |
| 🔐 **Login** | `http://<IP>:8000/login.html` | Đăng nhập admin |

## 🔐 Thông Tin Đăng Nhập

- **Username**: `admin`
- **Password**: `yep2025`

> **Lưu ý**: Đổi mật khẩu trong `api/login.php` trước khi triển khai thực tế.

## 📁 Cấu Trúc Dự Án

```
webvote/
├── index.html          # Giao diện bình chọn (Voter)
├── live.html           # Màn hình kết quả realtime (Projector)
├── qr.html             # Trang QR Code riêng
├── admin.html          # Giao diện quản trị viên
├── login.html          # Trang đăng nhập Admin
├── js/
│   ├── shared.js       # Utilities chung (time sync, countdown, toast)
│   ├── fingerprint.js  # Device fingerprinting logic
│   ├── vote.js         # Logic bình chọn (60s polling)
│   ├── live.js         # Logic biểu đồ + confetti + sound (3s polling)
│   └── admin.js        # Logic admin panel (10s polling)
├── api/                # Backend API (PHP)
│   ├── performers.php  # CRUD tiết mục
│   ├── votes.php       # Xử lý gửi phiếu, kiểm tra vote (Fingerprint/IP)
│   ├── results.php     # Tính toán điểm số & xếp hạng
│   ├── session.php     # Quản lý phiên bình chọn
│   ├── server_info.php # API lấy IP server cho QR
│   ├── export.php      # Xuất CSV/PDF
│   ├── upload.php      # Upload ảnh
│   └── login.php       # Xác thực Admin
├── data/               # Lưu trữ dữ liệu (JSON)
│   ├── performers.json # Danh sách tiết mục
│   ├── votes.json      # Dữ liệu phiếu bầu
│   └── session.json    # Trạng thái phiên
├── lib/
│   └── fpdf/           # Thư viện xuất PDF
└── uploads/            # Ảnh tiết mục
```

## 🛠️ Tech Stack

| Layer | Công nghệ |
|-------|-----------|
| **Frontend** | HTML5, Tailwind CSS (CDN), Vanilla JavaScript |
| **Charts** | Chart.js + chartjs-plugin-datalabels |
| **Audio** | Web Audio API (tones động) |
| **Confetti** | canvas-confetti (CDN) |
| **Icons** | Remix Icon |
| **Backend** | PHP 7.4+ (Native) |
| **Database** | JSON Files (không cần MySQL) |
| **PDF Export** | FPDF Library |

## 📝 API Endpoints

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| `GET` | `/api/performers.php` | Lấy danh sách tiết mục |
| `POST` | `/api/performers.php` | Thêm/Cập nhật tiết mục |
| `DELETE` | `/api/performers.php?id={id}` | Xóa tiết mục |
| `GET` | `/api/votes.php?check=1` | Kiểm tra trạng thái đã vote |
| `POST` | `/api/votes.php` | Gửi phiếu bầu (kèm fingerprint) |
| `DELETE` | `/api/votes.php` | Xóa tất cả phiếu (Admin) |
| `GET` | `/api/results.php` | Lấy kết quả xếp hạng |
| `POST` | `/api/session.php` | Mở/Đóng phiên (action: open/close) |
| `GET` | `/api/session.php` | Lấy trạng thái phiên hiện tại |
| `GET` | `/api/server_info.php` | Lấy IP server cho QR |
| `GET` | `/api/export.php?format=csv` | Xuất kết quả CSV |
| `GET` | `/api/export.php?format=pdf` | Xuất kết quả PDF |

## ⚠️ Lưu Ý Quan Trọng

1. **Reset Dữ Liệu**: Trước sự kiện, vào Admin → "Xóa tất cả phiếu" để xóa dữ liệu test.
2. **Mạng LAN**: Máy chủ và điện thoại phải cùng mạng Wifi. Dùng IP LAN thay vì `localhost`.
3. **Server Specs**: 
   - Tối thiểu: 1 vCPU, 1GB RAM (hỗ trợ ~100 voters)
   - Khuyến nghị: 2 vCPU, 2GB RAM (>200 voters)

## 📚 Tài Liệu Liên Quan

- [USER_GUIDE.md](USER_GUIDE.md) - Hướng dẫn sử dụng chi tiết cho BTC, Kỹ thuật, Khán giả
- [REVIEW.md](REVIEW.md) - Review kỹ thuật và roadmap tính năng

## 📜 License

MIT License - Sử dụng tự do cho mục đích thương mại và phi thương mại.
