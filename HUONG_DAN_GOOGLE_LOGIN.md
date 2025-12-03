# Hướng dẫn cài đặt Google Login

## Bước 1: Cài đặt thư viện Google API Client

Mở terminal tại thư mục gốc dự án và chạy lệnh:

```bash
composer install
```

Hoặc nếu chưa có composer.json:

```bash
composer require google/apiclient
```

**Lưu ý:** Nếu chưa cài đặt Composer, hãy tải về tại: https://getcomposer.org/download/

## Bước 2: Cấu hình Google OAuth

File `config/google_config.php` đã được tạo với thông tin:

- **Client ID:** `1051455658972-o9s8mp1prdutoovthigle264o2liqur0.apps.googleusercontent.com`
- **Client Secret:** `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`
- **Redirect URI:** `http://localhost/nguyenhuynhphuvinh-web_bandt_php/auth/google_callback.php`

**Quan trọng:** Bạn cần cập nhật `$redirectUri` trong file `config/google_config.php` để khớp với đường dẫn thực tế của dự án trên máy bạn.

## Bước 3: Cấu hình Google Cloud Console

1. Truy cập: https://console.cloud.google.com/
2. Chọn project hoặc tạo project mới
3. Vào **APIs & Services** > **Credentials**
4. Thêm **Authorized redirect URIs** với URL callback của bạn:
   - Ví dụ: `http://localhost/nguyenhuynhphuvinh-web_bandt_php/auth/google_callback.php`
   - Hoặc domain thực tế: `https://yourdomain.com/auth/google_callback.php`

## Bước 4: Kiểm tra Database

Đảm bảo bảng `users` có các cột sau:
- `google_id` VARCHAR(255) UNIQUE
- `avatar` VARCHAR(255)
- `email` VARCHAR(100) UNIQUE NOT NULL

File `CSDL.sql` đã được cập nhật với cấu trúc này.

## Bước 5: Test tính năng

1. Truy cập trang đăng nhập: `http://localhost/nguyenhuynhphuvinh-web_bandt_php/auth/login.php`
2. Click nút "Đăng nhập bằng Google"
3. Chọn tài khoản Google
4. Nếu là lần đầu, bạn sẽ được chuyển đến trang thiết lập mật khẩu
5. Nhập username và password mong muốn
6. Hoàn tất và đăng nhập

## Luồng hoạt động

### User mới (chưa có trong hệ thống):
1. Click "Đăng nhập bằng Google"
2. Chọn tài khoản Google
3. Hệ thống tạo user mới với status = 'inactive'
4. Chuyển đến trang `setup_password.php`
5. User nhập username và password
6. Tài khoản được kích hoạt (status = 'active')
7. Đăng nhập thành công

### User cũ (đã có trong hệ thống):
1. Click "Đăng nhập bằng Google"
2. Chọn tài khoản Google
3. Hệ thống kiểm tra email hoặc google_id
4. Nếu đã có password và status = 'active' → Đăng nhập ngay
5. Nếu chưa có password hoặc status = 'inactive' → Chuyển đến setup_password.php

## Files đã tạo/cập nhật

### Files mới:
- `config/google_config.php` - Cấu hình Google OAuth
- `auth/google_callback.php` - Xử lý callback từ Google
- `auth/setup_password.php` - Trang thiết lập mật khẩu
- `composer.json` - Quản lý dependencies

### Files đã cập nhật:
- `auth/login.php` - Thêm nút Google Login, cho phép đăng nhập bằng email
- `auth/register.php` - Thêm nút Google Register

## Troubleshooting

### Lỗi: "Class 'Google_Client' not found"
→ Chạy `composer install` để cài đặt thư viện

### Lỗi: "redirect_uri_mismatch"
→ Kiểm tra lại Redirect URI trong Google Cloud Console và file `google_config.php`

### Lỗi: "invalid_client"
→ Kiểm tra lại Client ID và Client Secret

### User không thể đăng nhập sau khi setup password
→ Kiểm tra status trong database, đảm bảo = 'active'

## Bảo mật

- Client Secret nên được lưu trong file `.env` hoặc config riêng (không commit lên Git)
- Thêm `.env` vào `.gitignore`
- Sử dụng HTTPS trong production
- Validate và sanitize tất cả input từ user
