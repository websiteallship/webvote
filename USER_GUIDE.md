# 📖 Hướng Dẫn Sử Dụng Hệ Thống Bình Chọn ALLSHIP GALA DINNER

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
1. **Thêm tiết mục mới**: Điền tên tiết mục, người trình diễn, upload ảnh, chọn màu đại diện → Bấm "Lưu".
2. **Sửa tiết mục**: Bấm icon ✏️ → Chỉnh sửa thông tin → Bấm "Lưu".
3. **Xóa tiết mục**: Bấm icon 🗑️ → Xác nhận xóa.

> 💡 **Mẹo**: Nên chuẩn bị ảnh đại diện tiết mục kích thước vuông (1:1) hoặc 4:3 để hiển thị đẹp nhất.

### 3. Điều Khiển Phiên Bình Chọn (Trong Giờ G)
Tại panel điều khiển phía trên tab "Tiết mục":

1. **Chọn thời gian**: Chọn thời lượng bình chọn (3, 5, 10, 15, 30 phút).
2. **Mở Phiên**: Bấm nút **"▶ Mở Phiên"**.
   - Hệ thống sẽ bắt đầu đếm ngược.
   - Cổng bình chọn trên điện thoại khán giả sẽ mở.
   - Màn hình Live sẽ hiển thị đồng hồ đếm ngược.
3. **Đóng Phiên**: 
   - Hết giờ: Hệ thống tự động đóng.
   - Bấm nút **"⏹ Đóng Phiên"**: Để kết thúc sớm nếu cần.

### 4. Xuất Kết Quả
Sau khi kết thúc bình chọn, trong tab **"Tiết mục"**:
- Bấm nút **"📥 Xuất CSV"** để tải file Excel.
- Bấm nút **"📄 Xuất PDF"** để tải báo cáo PDF.

### 5. Quản Lý Dữ Liệu (Reset)
- **Trước khi chạy chính thức**: Vào tab **"Tiết mục"** → Bấm nút **"🗑️ Xóa tất cả phiếu"** để xóa dữ liệu test.
- **Xem danh sách phiếu**: Chuyển sang tab **"Phiếu bầu"** để xem ai đã bầu cho ai.

---

## 📺 Phần 2: Dành cho Kỹ thuật Trình chiếu (Screen Operator)

### 1. Chuẩn Bị Màn Hình Kết Quả
Mở trang Live Results trên máy tính kết nối với màn hình LED/Projector:
- **Đường dẫn**: `http://<IP_MAY_CHU>:8000/live.html`
- Bấm **F11** để chế độ Toàn màn hình (Full Screen).

### 2. Các Tính Năng Trên Màn Hình Live

#### 📊 Biểu Đồ Đua (Racing Bar Chart)
- Tự động cập nhật mỗi **3 giây**.
- Hiển thị Top 3 tiết mục dẫn đầu.
- Đồng hồ đếm ngược khi phiên mở.

#### 🔊 Âm Thanh (Sound Effects)
- **Bật/Tắt âm thanh**: Bấm nút 🔊 ở góc phải màn hình.
- Âm thanh sẽ phát khi:
  - ✅ Có vote mới (tiếng "ting")
  - ⏰ Còn 10 giây cuối (beep cảnh báo)
  - 🔔 Hết giờ (chuông kết thúc)

> **Lưu ý**: Lần đầu mở trang, cần bấm "Bật âm thanh" để browser cho phép phát audio.

#### 🎉 Confetti (Pháo Giấy)
Khi MC công bố kết quả Top 3:
- Bấm nút **"🏆 Celebrate!"** trên thanh công cụ.
- Hoặc nhấn phím **'C'** trên bàn phím.
- Hiệu ứng pháo giấy rực rỡ sẽ bắn ra từ hai bên màn hình!

#### 📷 QR Code
- QR Code hiển thị ở góc trái dưới để khán giả quét.
- Để hiển thị QR lớn hơn: Truy cập `http://<IP_MAY_CHU>:8000/qr.html`

### 3. Kiểm Tra Trước Sự Kiện
- [ ] Mở trang `live.html` → Thấy danh sách tiết mục
- [ ] Quét thử QR → Vào được trang bình chọn
- [ ] Bấm "Bật âm thanh" → Nghe được tiếng
- [ ] Test vote thử → Thấy biểu đồ cập nhật
- [ ] Bấm Celebrate → Thấy confetti

> ⚠️ **Quan trọng**: Đảm bảo máy tính trình chiếu kết nối mạng ổn định. Không tắt tab trình duyệt!

---

## 📱 Phần 3: Dành cho Khán giả (Voter)

### 1. Truy Cập
- **Cách 1**: Quét mã QR Code hiển thị trên màn hình LED.
- **Cách 2**: Nhập đường link do BTC cung cấp vào trình duyệt.

### 2. Cách Bình Chọn
1. **Chọn 3 Tiết mục**: Bấm vào các tiết mục bạn yêu thích.
   - Lần chọn 1: 🥇 Hạng Nhất (3 điểm)
   - Lần chọn 2: 🥈 Hạng Nhì (2 điểm)
   - Lần chọn 3: 🥉 Hạng Ba (1 điểm)
2. **Thay đổi lựa chọn**: Bấm vào ô kết quả đã chọn để bỏ chọn và chọn lại.
3. **Gửi Phiếu**:
   - Sau khi chọn đủ 3 tiết mục, nút **"Gửi Bình Chọn"** sẽ sáng lên.
   - Bấm nút → Nhập Tên của bạn → Xác nhận.

### 3. Quy Định
- ⚠️ Mỗi người (mỗi thiết bị/tên) chỉ được bình chọn **1 lần duy nhất** trong mỗi phiên.
- ⏰ Chỉ được bình chọn khi MC thông báo "Bắt đầu" và phiên còn mở.
- 📶 Đảm bảo điện thoại kết nối **cùng mạng Wifi** với máy chủ (không dùng 4G).

---

## ❓ Câu Hỏi Thường Gặp (FAQ)

### Kỹ Thuật

**Q: Làm sao để lấy địa chỉ IP máy chủ?**
A: 
- Windows: Mở Command Prompt (`cmd`), gõ `ipconfig`. Tìm dòng `IPv4 Address` (ví dụ: 192.168.1.5).
- Mac: Mở Terminal, gõ `ifconfig` hoặc vào System Settings → Network.

**Q: Tại sao điện thoại không truy cập được trang web?**
A: 
1. Kiểm tra điện thoại và máy chủ có đang kết nối **cùng một mạng Wifi** không.
2. Nếu dùng 4G sẽ không truy cập được server nội bộ.
3. Đảm bảo Firewall trên máy chủ không chặn port 8000.

**Q: Âm thanh không phát trên trang Live?**
A: Trình duyệt yêu cầu tương tác người dùng trước khi phát audio. Hãy bấm nút "Bật âm thanh" khi popup hiện ra.

### Dữ Liệu

**Q: Tôi lỡ xóa nhầm tiết mục đang có điểm, có khôi phục được không?**
A: Không. Dữ liệu xóa là mất vĩnh viễn. Hãy cẩn trọng!

**Q: Có lưu được kết quả sau khi reset phiên không?**
A: Có! Trước khi reset, hãy **Xuất CSV/PDF** để lưu trữ kết quả.

---

## 📋 Checklist Sự Kiện

### Trước Sự Kiện (1 ngày)
- [ ] Nhập đầy đủ danh sách tiết mục
- [ ] Upload ảnh đại diện cho từng tiết mục
- [ ] Kiểm tra kết nối mạng LAN/Wifi
- [ ] Test thử toàn bộ quy trình (vote → live → export)

### Ngày Sự Kiện (Trước 30 phút)
- [ ] Bật máy chủ (`php -S 0.0.0.0:8000`)
- [ ] Mở trang `live.html` trên máy chiếu → F11 fullscreen
- [ ] Bật âm thanh trên trang Live
- [ ] Kiểm tra QR quét được
- [ ] **XÓA TẤT CẢ PHIẾU TEST**

### Trong Sự Kiện
- [ ] MC thông báo → Admin mở phiên
- [ ] Theo dõi đồng hồ đếm ngược
- [ ] Khi hết giờ → Bấm "Celebrate!" 🎉
- [ ] Công bố Top 3

### Sau Sự Kiện
- [ ] Xuất CSV/PDF kết quả
- [ ] Lưu trữ file backup
- [ ] Tắt server

---

## 🆘 Liên Hệ Hỗ Trợ

Nếu gặp sự cố kỹ thuật trong sự kiện, liên hệ ngay đội kỹ thuật IT.

---

*Cập nhật lần cuối: Tháng 1/2026*
