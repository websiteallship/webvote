# Hướng Dẫn Deploy Tự Động Từ GitHub Lên aaPanel

Tài liệu này hướng dẫn thiết lập quy trình tự động cập nhật code (CI/CD) từ GitHub repository lên server aaPanel tại đường dẫn `/www/wwwroot/vote-yep.allship.vn`.

Có 2 cách phổ biến. **Cách 1 (GitHub Actions)** là chuyên nghiệp và ổn định nhất.

---

## Chuẩn Bị Chung (Bắt buộc)

Trước khi làm bất kỳ cách nào, bạn cần đảm bảo code đã có trên server lần đầu tiên.

1.  **SSH vào server** hoặc dùng **Terminal** trong aaPanel.
2.  **Cài đặt Git** (nếu chưa có):
    ```bash
    yum install git -y  # CentOS
    # hoặc
    apt install git -y  # Ubuntu/Debian
    ```
3.  **Khởi tạo Repo trên Server**:
    *Lưu ý: Nếu thư mục đã có file, hãy backup trước.*
    ```bash
    # Di chuyển vào thư mục web root
    cd /www/wwwroot/vote-yep.allship.vn

    # Nếu thư mục trống, clone trực tiếp
    git clone https://github.com/websiteallship/webvote.git .

    # Nếu thư mục đã có code cũ, khởi tạo git
    git init
    git remote add origin https://github.com/websiteallship/webvote.git
    git fetch --all
    git reset --hard origin/main
    ```
4.  **Phân quyền lại cho user `www`** (Quan trọng để Web Server đọc ghi được):
    ```bash
    chown -R www:www /www/wwwroot/vote-yep.allship.vn
    ```

---

## Cách 1: Sử Dụng GitHub Actions (Khuyên Dùng)

Cách này giúp GitHub chủ động "bắn" tín hiệu và thực hiện lệnh update trên server server mỗi khi bạn push code.

### Bước 1: Tạo SSH Key
Để GitHub có thể truy cập server của bạn an toàn mà không cần mật khẩu.

1.  Trên máy cá nhân (hoặc Cloud Shell), tạo cặp key mới (không đặt passphrase):
    ```bash
    ssh-keygen -t rsa -b 4096 -C "github-actions" -f gh_deploy_key
    ```
2.  Sẽ tạo ra 2 file: `gh_deploy_key` (Private) và `gh_deploy_key.pub` (Public).
3.  **Trên Server aaPanel**:
    *   Mở file `/root/.ssh/authorized_keys` (hoặc tạo nếu chưa có).
    *   Copy nội dung file `gh_deploy_key.pub` dán vào cuối file đó.
    *   Lưu lại và chmod an toàn: `chmod 600 /root/.ssh/authorized_keys`.

### Bước 2: Cấu hình Secrets trên GitHub
1.  Vào Repo trên GitHub → **Settings** → **Secrets and variables** → **Actions**.
2.  Bấm **New repository secret** và thêm các biến sau:
    *   `HOST`: Địa chỉ IP của server aaPanel.
    *   `USERNAME`: `root`
    *   `SSH_PRIVATE_KEY`: Copy toàn bộ nội dung file `gh_deploy_key` (Private key) vừa tạo.

### Bước 3: Tạo Workflow File
Trong thư mục dự án trên máy tính của bạn, tạo file `.github/workflows/deploy.yml`:

```yaml
name: Deploy to aaPanel

on:
  push:
    branches:
      - main  # Hoặc master tùy branch chỉnh của bạn

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Deploy via SSH
        uses: appleboy/ssh-action@v1.0.0
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          port: 22
          script: |
            # 1. Di chuyển vào thư mục dự án
            cd /www/wwwroot/vote-yep.allship.vn
            
            # 2. Pull code mới nhất
            git pull origin main
            
            # 3. Phân quyền lại cho user www (để tránh lỗi permission denied khi web chạy)
            chown -R www:www .
            
            # 4. (Tùy chọn) Xóa cache log nếu cần
            # rm -rf var/cache/*
            
            echo "Deployment successful! 🚀"
```

### Bước 4: Push và Test
Commit và push file `.github/workflows/deploy.yml` lên GitHub. Qua tab **Actions** trên GitHub để xem tiến trình chạy.

---

## Cách 2: Sử Dụng aaPanel Webhook (Đơn giản hơn cho người không rành SSH Keys)

aaPanel có plugin Webhook hỗ trợ việc này.

1.  **Cài Plugin**: Trong App Store của aaPanel, tìm và cài **"Webhook"**.
2.  **Tạo Hook**:
    *   Mở Webhook, bấm **Add**.
    *   **Title**: Deploy Code
    *   **Execution Script**:
        ```bash
        #!/bin/bash
        echo "Start deployment..."
        cd /www/wwwroot/vote-yep.allship.vn
        git pull origin main
        chown -R www:www .
        echo "Done!"
        ```
    *   Bấm Submit.
3.  **Lấy Link**: Bấm nút **View** (View keys) để lấy URL webhook vừa tạo.
    *   URL sẽ có dạng: `http://YOUR_IP:8888/hook?access_key=...&param=...`
4.  **Cấu hình trên GitHub**:
    *   Vào Repo GitHub → **Settings** → **Webhooks** → **Add webhook**.
    *   **Payload URL**: Dán link Webhook ở trên vào.
    *   **Content type**: `application/json` (thực ra không quan trọng với script này).
    *   **Secret**: Để trống.
    *   Bấm **Add webhook**.

**Lưu ý với cách 2**: Bạn cần đảm bảo server đã lưu thông tin đăng nhập GitHub (lệnh `git config --global credential.helper store`) hoặc dùng SSH Key cho Github account trên server để lệnh `git pull` không bị hỏi mật khẩu user/pass.

---

## ⚠️ Các lỗi thường gặp

1.  **Lỗi: `Permission denied` khi chạy web sau khi deploy**
    *   Nguyên nhân: Khi pull bằng user `root`, các file mới sẽ thuộc quyền `root`. Web server (Nginx/Apache) chạy bằng user `www` không ghi đè được.
    *   Khắc phục: Luôn có dòng `chown -R www:www .` cuối script deploy.

2.  **Lỗi: `Conflict` khi git pull**
    *   Nguyên nhân: Bạn đã sửa file trực tiếp trên server aaPanel.
    *   Khắc phục:
        ```bash
        git reset --hard origin/main
        git pull origin main
        ```
        *(Cảnh báo: Lệnh này sẽ xóa mọi thay đổi bạn làm trực tiếp trên server)*.
