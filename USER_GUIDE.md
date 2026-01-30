# 📖 Hướng Dẫn Sử Dụng Hệ Thống Bình Chọn YEP 2025

Tài liệu hướng dẫn chi tiết dành cho Ban Tổ Chức (BTC), Bộ phận Kỹ thuật Trình chiếu, và Người tham dự sự kiện.

---

## 🎭 Phần 1: Dành cho Ban Tổ Chức (Admin)

### 1. Đăng Nhập Hệ Thống
Truy cập trang quản trị bằng trình duyệt web (Chrome, Edge, Safari, Firefox):
- **Đường dẫn**: `http://<IP_MAY_CHU>:8000/admin.html`
- **Username**: `admin`
- **Password**: `yep2025`

*(Lưu ý: Thay `<IP_MAY_CHU>` bằng địa chỉ IP của máy chủ chạy website)*

### 2. Quản Lý Tiết Mục (Trước Giờ G)
Trong tab **"Tiết mục"**:
1. **Thêm tiết mục mới**: Điền tên tiết mục, người trình diễn, link ảnh (hoặc upload ảnh), chọn màu đại diện -> Bấm "Lưu".
2. **Sửa tiết mục**: Bấm icon ✏️ -> Chỉnh sửa thông tin -> Bấm "Lưu".
3. **Xóa tiết mục**: Bấm icon 🗑️ -> Xác nhận xóa.

> 💡 **Mẹo**: Nên chuẩn bị ảnh đại diện tiết mục kích thước vuông (1:1) hoặc 4:3 để hiển thị đẹp nhất.

### 3. Điều Khiển Phiên Bình Chọn (Trong Giờ G)
Tại panel điều khiển màu tím/hồng phía trên tab "Tiết mục":

1. **Chọn thời gian**: Chọn thời lượng bình chọn (3, 5, 10, 15, 30 phút).
2. **Mở Phiên**: Bấm nút **"▶ Mở Phiên"**.
   - Hệ thống sẽ bắt đầu đếm ngược.
   - Cổng bình chọn trên điện thoại khán giả sẽ mở.
   - Màn hình Live sẽ hiển thị đồng hồ đếm ngược.
3. **Đóng Phiên**: 
   - Hết giờ: Hệ thống tự động đóng.
   - Bấm nút **"⏹ Đóng Phiên"**: Để kết thúc sớm nếu cần.

### 4. Quản Lý Dữ Liệu (Reset)
- **Trước khi chạy chính thức**: Vào tab **"Tiết mục"** -> Bấm nút **"🗑️ Xóa tất cả phiếu"** để xóa toàn bộ dữ liệu test.
- **Xem danh sách phiếu**: Chuyển sang tab **"Phiếu bầu"** để xem ai đã bầu cho ai, thời gian nào.

---

## 📺 Phần 2: Dành cho Kỹ thuật Trình chiếu (Screen Operator)

### 1. Chuẩn Bị Màn Hình
Mở trang Live Results trên máy tính kết nối với màn hình LED/Projector:
- **Đường dẫn**: `http://<IP_MAY_CHU>:8000/live.html`
- Bấm **F11** để chế độ Toàn màn hình (Full Screen).

### 2. Theo Dõi Trực Tiếp
- Màn hình này sẽ **TỰ ĐỘNG** cập nhật:
  - ⏱️ Đồng hồ đếm ngược khi phiên mở.
  - 📊 Biểu đồ đua (Racing Bar Chart) thay đổi điểm số real-time mỗi 3 giây.
  - 🏆 Top 3 tiết mục dẫn đầu.

> ⚠️ **Lưu ý quan trọng**: Đảm bảo máy tính trình chiếu luôn kết nối mạng ổn định với máy chủ. Không tắt tab trình duyệt này trong quá trình bình chọn.

---

## 📱 Phần 3: Dành cho Khán giả (Voter)

### 1. Truy Cập
- Quét mã QR Code hiển thị trên màn hình.
- Hoặc truy cập đường dẫn do BTC cung cấp.

### 2. Cách Bình Chọn
1. **Chọn 3 Tiết mục**: Bấm vào các tiết mục bạn yêu thích.
   - Lần chọn 1: 🥇 Hạng Nhất (3 điểm)
   - Lần chọn 2: 🥈 Hạng Nhì (2 điểm)
   - Lần chọn 3: 🥉 Hạng Ba (1 điểm)
2. **Thay đổi lựa chọn**: Bấm vào ô kết quả đã chọn để bỏ chọn và chọn lại.
3. **Gửi Phiếu**:
   - Sau khi chọn đủ 3 tiết mục, nút **"Gửi Bình Chọn"** sẽ sáng lên.
   - Bấm nút -> Nhập Tên của bạn -> Xác nhận.

### 3. Quy Định
- Mỗi người (mỗi thiết bị/tên) chỉ được bình chọn **1 lần duy nhất** trong mỗi phiên.
- Chỉ được bình chọn khi MC thông báo "Bắt đầu".

---

## ❓ Câu Hỏi Thường Gặp (FAQ)

**Q: Làm sao để lấy địa chỉ IP máy chủ?**
A: 
- Trên Windows: Mở Command Prompt (`cmd`), gõ `ipconfig`. Tìm dòng `IPv4 Address` (ví dụ: 192.168.1.5).
- Trên Mac: Mở Terminal, gõ `ifconfig` hoặc vào System Settings -> Network.

**Q: Tại sao tôi không truy cập được trang web trên điện thoại?**
A: Kiểm tra xem điện thoại và máy chủ có đang kết nối **cùng một mạng Wifi** không. Nếu dùng 4G sẽ không truy cập được server nội bộ (Localhost).

**Q: Tôi lỡ xóa nhầm tiết mục đang có điểm, có khôi phục được không?**
A: Không. Dữ liệu xóa là mất vĩnh viễn. Hãy cẩn trọng!
