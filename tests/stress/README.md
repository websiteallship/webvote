# Hướng Dẫn Stress Test Dự Án WebVote

Tài liệu này hướng dẫn cách chạy stress test mô phỏng **100 người dùng đồng thời** bằng công cụ **k6**.

## 1. Tại sao chạy trên máy tính cá nhân (Recommended)?
Chạy từ máy local (Windows/Mac) của bạn gửi request lên server (aaPanel) sẽ cho kết quả trung thực nhất vì:
- Kiểm tra được tốc độ mạng thực tế.
- Không chiếm dụng CPU của server để tạo traffic giả (tránh tình trạng "tự mình hại mình").

## 2. Cài đặt k6

### Trên Windows
1. Tải bộ cài đặt (MSI) mới nhất tại: [https://dl.k6.io/msi/k6-latest-amd64.msi](https://dl.k6.io/msi/k6-latest-amd64.msi)
2. Cài đặt như phần mềm bình thường.
3. Mở CMD hoặc PowerShell, gõ `k6 version` để kiểm tra.

### Trên Linux (aaPanel Terminal) - Nếu bắt buộc chạy trên server
```bash
sudo gpg -k
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6
```
*(Nếu dùng CentOS/RedHat, xem hướng dẫn tại k6.io/docs/get-started/installation)*

## 3. Cấu hình & Chạy Test

### Bước 1: Chuẩn bị server
Đảm bảo bạn đã mở phiên bầu chọn (`session.php` status = open) trên trang Admin, nếu không các vote có thể bị từ chối.

### Bước 2: Chạy lệnh test
Mở terminal tại thư mục `tests/stress/` và chạy lệnh sau (thay URL bằng domain thật của bạn):

```powershell
# Chạy stress test 100 user
k6 run -e BASE_URL=https://your-domain.com k6_vote_scenario.js
```

### Bước 3: Đọc kết quả
Quan sát các chỉ số sau trong bảng kết quả:
- **`http_req_duration`**: Thời gian phản hồi trung bình (nên < 2000ms).
- **`http_req_failed`**: Tỷ lệ lỗi (nên là 0.00%).
- **`vus`**: Số người dùng ảo đang hoạt động.

## 4. Troubleshooting
- **Lỗi `checks.........: 50.00%`**: Server đang trả về lỗi (có thể lỗi PHP hoặc quá tải). Kiểm tra log server trên aaPanel.
- **Lỗi `connection refused`**: Kiểm tra lại URL hoặc Firewall của aaPanel.
