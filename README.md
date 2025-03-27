<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Hệ Thống Quản Lý Thông Tin Y Tế

Hệ thống quản lý thông tin y tế được xây dựng bằng Laravel, cung cấp các chức năng quản lý thông tin về bệnh và thuốc, tích hợp chatbot AI để hỗ trợ người dùng.

## Tính Năng Chính

- Quản lý thông tin bệnh và thuốc
- Tìm kiếm thông minh với gợi ý
- Chatbot AI hỗ trợ tư vấn
- Phân quyền người dùng (Admin/User)
- Quản lý danh sách yêu thích
- Import dữ liệu từ CSV
- Giao diện thân thiện, responsive

## Yêu Cầu Hệ Thống

- PHP >= 8.0
- Composer
- MySQL >= 5.7
- Node.js >= 14.x
- NPM hoặc Yarn

## Cài Đặt

1. Clone repository:
```bash
git clone https://github.com/your-username/medical-info-system.git
cd medical-info-system
```

2. Cài đặt các dependencies:
```bash
composer install
npm install
```

3. Tạo file .env:
```bash
cp .env.example .env
```

4. Tạo key cho ứng dụng:
```bash
php artisan key:generate
```

5. Cấu hình database trong file .env:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Chạy migration:
```bash
php artisan migrate
```

7. Tạo dữ liệu mẫu (tùy chọn):
```bash
php artisan db:seed
```

8. Biên dịch assets:
```bash
npm run dev
```

9. Chạy server:
```bash
php artisan serve
```

## Cấu Hình Chatbot AI

1. Đăng ký tài khoản tại DeepSeek AI
2. Lấy API key
3. Cấu hình trong file .env:
```
DEEPSEEK_API_KEY=your_api_key
```

## Hướng Dẫn Sử Dụng

### Cho Người Dùng Thường

1. **Xem Danh Sách Bệnh/Thuốc**
   - Truy cập trang chủ
   - Sử dụng thanh tìm kiếm để tìm kiếm bệnh hoặc thuốc
   - Xem chi tiết bằng cách nhấn nút "Chi tiết"

2. **Sử Dụng Chatbot**
   - Nhấn nút chat ở góc phải màn hình
   - Đặt câu hỏi về bệnh hoặc thuốc
   - Nhận phản hồi từ AI

3. **Quản Lý Danh Sách Yêu Thích**
   - Đăng nhập vào tài khoản
   - Nhấn nút "Lưu" trên bệnh/thuốc muốn yêu thích
   - Thêm ghi chú (tùy chọn)
   - Xem danh sách yêu thích trong menu

### Cho Quản Trị Viên

1. **Quản Lý Bệnh**
   - Thêm bệnh mới
   - Sửa thông tin bệnh
   - Xóa bệnh
   - Import danh sách bệnh từ CSV

2. **Quản Lý Thuốc**
   - Thêm thuốc mới
   - Sửa thông tin thuốc
   - Xóa thuốc
   - Import danh sách thuốc từ CSV

3. **Quản Lý Người Dùng**
   - Xem danh sách người dùng
   - Phân quyền người dùng
   - Khóa/mở khóa tài khoản

## Đóng Góp

1. Fork repository
2. Tạo branch mới (`git checkout -b feature/AmazingFeature`)
3. Commit các thay đổi (`git commit -m 'Add some AmazingFeature'`)
4. Push lên branch (`git push origin feature/AmazingFeature`)
5. Mở Pull Request

## Báo Cáo Lỗi

Nếu bạn phát hiện lỗi, vui lòng:
1. Kiểm tra xem lỗi đã được báo cáo chưa
2. Tạo issue mới với mô tả chi tiết
3. Đính kèm screenshot hoặc video nếu cần

## Liên Hệ

- Website: [your-website]
- Email: [your-email]
- Facebook: [your-facebook]
- Twitter: [your-twitter]

## License

Dự án được phân phối dưới giấy phép MIT. Xem `LICENSE` để biết thêm thông tin.
