<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hệ thống quản lý thuốc và bệnh')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #0d6efd;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 20px 0;
            margin-top: 40px;
        }
        
        /* CSS tùy chỉnh cho phân trang */
        .pagination {
            margin-top: 30px;
        }
        
        .pagination .page-item .page-link {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6;
            padding: 0.5rem 0.75rem;
            margin: 0 3px;
            font-size: 0.9rem;
            line-height: 1.25;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
            box-shadow: 0 2px 5px rgba(13, 110, 253, 0.3);
            font-weight: bold;
        }
        
        .pagination .page-item .page-link:hover {
            color: #0d6efd;
            background-color: #e9ecef;
            border-color: #dee2e6;
            transform: scale(1.1);
        }
        
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
        }
        
        /* Làm nổi bật nút phân trang khi hover */
        .pagination .page-item:not(.disabled):not(.active) .page-link:hover {
            background-color: #f8f9fa;
            border-color: #0d6efd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        /* CSS cho avatar người dùng */
        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        /* CSS cho dropdown menu */
        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: none;
            padding: 8px 0;
        }
        
        .dropdown-item {
            padding: 8px 20px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: #f0f7ff;
        }
        
        .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
        }
        
        .dropdown-divider {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">PhenikaaMedHelper</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="fas fa-home me-1"></i>Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('medicines') }}">
                            <i class="fas fa-pills me-1"></i>Danh sách thuốc
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('diseases') }}">
                            <i class="fas fa-virus me-1"></i>Danh sách bệnh
                        </a>
                    </li>
                    
                    @if(Session::has('user') && Session::get('user')['role'] == 'admin')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.users') }}">
                            <i class="fas fa-users me-1"></i>Quản lý người dùng
                        </a>
                    </li>
                    @endif
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    @if(Session::has('user'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava3.webp" alt="avatar" class="user-avatar">
                                {{ Session::get('user')['name'] ?? Session::get('user')['username'] }}
                                @if(Session::get('user')['role'] == 'admin')
                                <span class="badge bg-danger ms-1">Admin</span>
                                @endif
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fas fa-user"></i>Thông tin cá nhân</a></li>
                                <li><a class="dropdown-item" href="{{ route('favorite-medicines') }}"><i class="fas fa-bookmark"></i>Thuốc đã lưu</a></li>
                                <li><a class="dropdown-item" href="{{ route('favorite-diseases') }}"><i class="fas fa-bookmark"></i>Bệnh đã lưu</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt"></i>Đăng xuất
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-1"></i>Đăng ký
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        @yield('content')
    </div>
    
    <!-- Footer -->
    <footer class="footer mt-auto py-5">
        <div class="container">
            <div class="row">
                <!-- Thông tin về Phenikaa MedHelper -->
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-uppercase mb-4">PhenikaaMedHelper</h5>
                    <p class="text-light">
                        Hệ thống tra cứu thông tin về thuốc và bệnh, giúp người dùng nắm bắt kiến thức y tế cơ bản và các phương pháp điều trị.
                    </p>
                    <div class="mt-4">
                        <a href="#" class="btn btn-outline-light btn-floating me-2" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-floating me-2" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-floating me-2" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-floating" aria-label="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Liên kết nhanh -->
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-uppercase mb-4">Liên kết nhanh</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="{{ route('home') }}" class="text-white text-decoration-none">
                                <i class="fas fa-home me-2"></i>Trang chủ
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('medicines') }}" class="text-white text-decoration-none">
                                <i class="fas fa-pills me-2"></i>Danh sách thuốc
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="{{ route('diseases') }}" class="text-white text-decoration-none">
                                <i class="fas fa-virus me-2"></i>Danh sách bệnh
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Thông tin liên hệ -->
                <div class="col-md-4">
                    <h5 class="text-uppercase mb-4">Liên hệ</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>Nhóm sinh viên
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2"></i>0899813596
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>info@phenikaaMedHelper.edu.vn
                        </li>
                        <li>
                            <i class="fas fa-clock me-2"></i>Thứ Hai - Thứ Sáu: 8:00 - 17:00
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="text-center py-3 mt-4" style="background-color: rgba(0, 0, 0, 0.2);">
            <p class="mb-0">
                © {{ date('Y') }} PhenikaaMedHelper - Hệ thống quản lý thuốc và bệnh
                <br>
                <small>Thiết kế và phát triển bởi nhóm SV Đồ án liên ngành</small>
            </p>
        </div>
    </footer>
    <!-- End Footer -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>