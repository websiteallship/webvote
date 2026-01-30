# YEP 2025 - Website Bình Chọn Tiết Mục

Dự án website bình chọn trực tuyến cho sự kiện YEP 2025, hỗ trợ tính năng **Ranked Choice Voting** (bình chọn theo thứ hạng) và hiển thị kết quả **Real-time Racing Bar Chart**.

## ✨ Tính Năng Nổi Bật

- 🗳️ **Ranked Choice Voting**: Người dùng chọn 3 tiết mục yêu thích nhất theo thứ tự:
    - 🥇 Hạng 1: **3 điểm**
    - 🥈 Hạng 2: **2 điểm**
    - 🥉 Hạng 3: **1 điểm**
- 📊 **Real-time Live Chart**: Biểu đồ đua (Racing Bar Chart) cập nhật liên tục mỗi 3 giây, tạo hiệu ứng kịch tính.
- ⏱️ **Quản Lý Phiên Bình Chọn**: Admin có thể mở/đóng phiên bình chọn và thiết lập thời gian đếm ngược (3, 5, 10... phút).
- 🛡️ **Kiểm Soát Gian Lận**:
    - Ngăn chặn trùng IP trong cùng một phiên.
    - Ngăn chặn trùng tên người bình chọn trong cùng một phiên.
    - Chặn thao tác bình chọn khi phiên chưa mở hoặc đã kết thúc.
- 📱 **Responsive Design**: Giao diện tối ưu hoàn hảo cho cả Mobile (người bình chọn) và Desktop/Projector (màn hình LED sự kiện).
- ⚙️ **Admin Panel Mạnh Mẽ**:
    - Thêm/Sửa/Xóa tiết mục (hỗ trợ upload ảnh, chọn màu đại diện).
    - Xem danh sách chi tiết từng phiếu bầu (Thời gian, Người bầu, IP, Thiết bị).
    - Xóa toàn bộ dữ liệu để reset hệ thống.

## 🚀 Cài Đặt & Chạy (Local)

### Yêu cầu hệ thống
- **PHP**: Phiên bản 7.4 trở lên.
- **Web Server**: Apache/Nginx hoặc PHP built-in server.
- **Quyền ghi**: Thư mục `data/` và `uploads/` cần có quyền ghi (777 hoặc quyền user web).

### Hướng dẫn chạy nhanh
1. Di chuyển vào thư mục dự án:
   ```bash
   cd webvote
   ```
2. Khởi động PHP Server:
   ```bash
   php -S 0.0.0.0:8000
   ```
3. Truy cập:
   - **Trang bình chọn (Voter)**: `http://localhost:8000` (hoặc IP LAN của máy chủ).
   - **Màn hình Live (Projector)**: `http://localhost:8000/live.html`
   - **Trang quản trị (Admin)**: `http://localhost:8000/admin.html`

## 🔐 Thông Tin Đăng Nhập

Truy cập trang quản trị tại: [`/admin.html`](http://localhost:8000/admin.html)

- **Username**: `admin`
- **Password**: `yep2025`

> **Lưu ý**: Thông tin đăng nhập được cấu hình trong file `api/login.php`. Hãy đổi mật khẩu khi triển khai thực tế.

## 📁 Cấu Trúc Dự Án

```
webvote/
├── index.html          # Giao diện bình chọn cho người dùng
├── live.html           # Giao diện hiển thị kết quả (Racing Bar Chart)
├── admin.html          # Giao diện quản trị viên
├── login.html          # Trang đăng nhập Admin
├── js/
│   ├── vote.js         # Logic xử lý bình chọn (Client)
│   └── live.js         # Logic xử lý cập nhật biểu đồ (Client)
├── api/                # Backend API (PHP)
│   ├── performers.php  # CRUD tiết mục
│   ├── votes.php       # Xử lý gửi phiếu, kiểm tra điều kiện
│   ├── results.php     # Tính toán điểm số & xếp hạng
│   ├── session.php     # Quản lý trạng thái phiên bình chọn
│   ├── upload.php      # Xử lý upload ảnh
│   └── login.php       # Xác thực Admin
├── data/               # Nơi lưu trữ dữ liệu (JSON)
│   ├── performers.json # Danh sách tiết mục
│   ├── votes.json      # Dữ liệu phiếu bầu
│   └── session.json    # Trạng thái phiên hiện tại
└── uploads/            # Thư mục lưu ảnh tiết mục
```

## 🛠️ Tech Stack

- **Frontend**: HTML5, Tailwind CSS (CDN), Vanilla JavaScript.
- **Charts**: Chart.js + chartjs-plugin-datalabels.
- **Icons**: Remix Icon.
- **Backend**: PHP (Native, không Framework).
- **Database**: JSON Files (NoSQL-like storage, không cần cài đặt MySQL).

## 🧩 Cài đặt Skills Nâng Cao (AI Agents)

Để tăng cường khả năng của AI Agent (Cursor/Windsurf/Antigravity) khi làm việc với dự án này, bạn có thể cài đặt thêm các bộ skills chuyên dụng vào thư mục `.agent`.

### 1. UI/UX Pro Max Skill
Bộ skill giúp tối ưu hóa giao diện và trải nghiệm người dùng theo chuẩn hiện đại.

```bash
# Di chuyển vào thư mục skills của agent
mkdir -p .agent/skills
cd .agent/skills

# Clone repository
git clone https://github.com/nextlevelbuilder/ui-ux-pro-max-skill.git ui-ux-pro-max
```

### 2. Antigravity Awesome Skills
Bộ sưu tập các skills mạnh mẽ cho Antigravity Agent.

```bash
# Tại thư mục .agent/skills (nếu chưa vào)
cd .agent/skills

# Clone repository
git clone https://github.com/sickn33/antigravity-awesome-skills.git antigravity-awesome
```

> **Lưu ý**: Sau khi cài đặt, hãy reload lại window (Developer: Reload Window) hoặc restart IDE để Agent nhận diện skills mới.

## 📝 API Endpoints

| Method | Endpoint | Mô tả |
| :--- | :--- | :--- |
| `GET` | `/api/performers.php` | Lấy danh sách tiết mục |
| `POST` | `/api/performers.php` | Thêm mới / Cập nhật tiết mục |
| `DELETE` | `/api/performers.php?id={id}` | Xóa tiết mục |
| `GET` | `/api/votes.php?check=1` | Kiểm tra trạng thái đã bầu chọn của user |
| `POST` | `/api/votes.php` | Gửi phiếu bầu mới |
| `GET` | `/api/results.php` | Lấy kết quả xếp hạng & điểm số |
| `POST` | `/api/session.php` | Mở/Đóng phiên bình chọn (action: open/close) |

## ⚠️ Lưu Ý Quan Trọng
1. **Reset Dữ Liệu**: Trước khi bắt đầu sự kiện chính thức, hãy vào Admin -> "Xóa tất cả phiếu" để đảm bảo tính công bằng.
2. **Mạng LAN**: Để người dùng truy cập được bằng điện thoại, máy chủ (laptop chạy PHP) và điện thoại phải cùng mạng Wifi. Thay `localhost` bằng địa chỉ IP của máy (ví dụ `192.168.1.x`).
