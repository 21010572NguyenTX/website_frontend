@extends('layouts.app')

@section('title', 'Thông tin cá nhân')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4 shadow-sm border-0 rounded-3">
                <div class="card-body text-center">
                    <div class="position-relative mb-4 mx-auto" style="width: 150px; height: 150px;">
                        <img src="{{ $user['avatar'] ?? 'https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava3.webp' }}" 
                            alt="avatar" class="rounded-circle img-fluid shadow" style="width: 150px; height: 150px; object-fit: cover;" id="profile-avatar">
                        <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle p-2 shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <h4 class="mb-1 fw-bold" id="profile-name">{{ $user['name'] ?? $user['username'] }}</h4>
                    <p class="text-muted mb-3 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user-circle me-2"></i><span id="profile-username">{{ $user['username'] }}</span>
                    </p>
                    <div class="d-grid gap-2 mb-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa thông tin
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="fas fa-key me-2"></i>Đổi mật khẩu
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Thẻ Thống kê hoạt động -->
            <div class="card mb-4 shadow-sm border-0 rounded-3">
                <div class="card-header bg-light border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>Thống kê hoạt động</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="p-3">
                                <h5 class="fw-bold text-primary mb-1">0</h5>
                                <p class="small text-muted mb-0">Thuốc đã lưu</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3">
                                <h5 class="fw-bold text-danger mb-1">0</h5>
                                <p class="small text-muted mb-0">Bệnh đã lưu</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3">
                                <h5 class="fw-bold text-success mb-1">0</h5>
                                <p class="small text-muted mb-0">Hỏi đáp</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- Thông báo kết quả -->
            <div id="alert-container"></div>
            
            <div class="card mb-4 shadow-sm border-0 rounded-3">
                <div class="card-header bg-gradient-primary-to-secondary text-white border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-id-card me-2"></i>Thông tin cá nhân</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-bold text-muted"><i class="fas fa-user me-2 text-primary"></i>Họ và tên</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0 text-dark" id="info-name">{{ $user['name'] ?? 'Chưa cập nhật' }}</p>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-bold text-muted"><i class="fas fa-user-tag me-2 text-primary"></i>Tên đăng nhập</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0 text-dark" id="info-username">{{ $user['username'] }}</p>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-bold text-muted"><i class="fas fa-fingerprint me-2 text-primary"></i>ID người dùng</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0 text-dark" id="info-id">{{ $user['id'] ?? 'Không xác định' }}</p>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-bold text-muted"><i class="fas fa-envelope me-2 text-primary"></i>Email</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0 text-dark" id="info-email">{{ $user['email'] ?? $user['id'] ?? 'Chưa cập nhật' }}</p>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-bold text-muted"><i class="fas fa-phone me-2 text-primary"></i>Số điện thoại</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0 text-dark" id="info-phone">{{ $user['phone'] ?? 'Chưa cập nhật' }}</p>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-bold text-muted"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Địa chỉ</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0 text-dark" id="info-address">{{ $user['address'] ?? 'Chưa cập nhật' }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <p class="mb-0 fw-bold text-muted"><i class="fas fa-calendar-alt me-2 text-primary"></i>Ngày tham gia</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0 text-dark">{{ isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : 'Không xác định' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4 shadow-sm border-0 rounded-3">
                <div class="card-header bg-gradient-primary-to-secondary text-white border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Hoạt động gần đây</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-dot bg-primary"><i class="fas fa-user-plus"></i></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Đăng ký tài khoản</h6>
                                <p class="text-muted small mb-0">{{ isset($user['created_at']) ? date('d/m/Y H:i', strtotime($user['created_at'])) : 'Không xác định' }}</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-dot bg-success"><i class="fas fa-sign-in-alt"></i></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Đăng nhập lần cuối</h6>
                                <p class="text-muted small mb-0">{{ isset($user['last_login']) ? date('d/m/Y H:i', strtotime($user['last_login'])) : date('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chỉnh sửa thông tin -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-gradient-primary-to-secondary text-white border-0">
                <h5 class="modal-title" id="editProfileModalLabel"><i class="fas fa-user-edit me-2"></i>Chỉnh sửa thông tin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="edit-profile-form" enctype="multipart/form-data">
                    <div class="mb-4 text-center">
                        <div class="position-relative d-inline-block">
                            <img src="{{ $user['avatar'] ?? 'https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava3.webp' }}" 
                                class="rounded-circle img-fluid mb-3 shadow" style="width: 120px; height: 120px; object-fit: cover;" id="preview-avatar">
                            <label for="avatar" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle p-2 shadow-sm">
                                <i class="fas fa-camera"></i>
                            </label>
                            <input type="file" name="avatar" id="avatar" class="d-none" accept="image/*" onchange="previewImage(this)">
                        </div>
                        <p class="text-muted small">Nhấn vào biểu tượng máy ảnh để cập nhật ảnh đại diện</p>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="name" name="name" value="{{ $user['name'] ?? '' }}" placeholder="Họ và tên" required>
                        <label for="name"><i class="fas fa-user me-2"></i>Họ và tên</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" value="{{ $user['email'] ?? '' }}" placeholder="Email">
                        <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ $user['phone'] ?? '' }}" placeholder="Số điện thoại">
                        <label for="phone"><i class="fas fa-phone me-2"></i>Số điện thoại</label>
                    </div>
                    <div class="form-floating mb-4">
                        <textarea class="form-control" id="address" name="address" rows="3" placeholder="Địa chỉ" style="height: 100px">{{ $user['address'] ?? '' }}</textarea>
                        <label for="address"><i class="fas fa-map-marker-alt me-2"></i>Địa chỉ</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-3 rounded-3" id="update-profile-btn">
                            <i class="fas fa-save me-2"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Đổi mật khẩu -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-gradient-primary-to-secondary text-white border-0">
                <h5 class="modal-title" id="changePasswordModalLabel"><i class="fas fa-shield-alt me-2"></i>Đổi mật khẩu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="change-password-form">
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="Mật khẩu hiện tại">
                        <label for="current_password"><i class="fas fa-lock me-2"></i>Mật khẩu hiện tại</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="new_password" name="new_password" required placeholder="Mật khẩu mới">
                        <label for="new_password"><i class="fas fa-key me-2"></i>Mật khẩu mới</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required placeholder="Xác nhận mật khẩu mới">
                        <label for="new_password_confirmation"><i class="fas fa-check-circle me-2"></i>Xác nhận mật khẩu mới</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-3 rounded-3" id="change-password-btn">
                            <i class="fas fa-key me-2"></i>Cập nhật mật khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Function for previewing the selected avatar image
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('preview-avatar').src = e.target.result;
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Function to show alerts
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alert-container');
        const alertHTML = `
            <div class="alert alert-${type} alert-dismissible fade show shadow-sm rounded-3 mb-4" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        alertContainer.innerHTML = alertHTML;
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
    
    // Function to update profile information in the UI
    function updateProfileUI(userData) {
        // Update profile header
        document.getElementById('profile-name').textContent = userData.name || userData.username;
        if (userData.avatar) {
            document.getElementById('profile-avatar').src = userData.avatar;
        }
        
        // Update profile information
        document.getElementById('info-name').textContent = userData.name || 'Chưa cập nhật';
        document.getElementById('info-email').textContent = userData.email || 'Chưa cập nhật';
        document.getElementById('info-phone').textContent = userData.phone || 'Chưa cập nhật';
        document.getElementById('info-address').textContent = userData.address || 'Chưa cập nhật';
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Handle profile update form submission
        const editProfileForm = document.getElementById('edit-profile-form');
        const updateProfileBtn = document.getElementById('update-profile-btn');
        
        editProfileForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Disable the button and show loading state
            updateProfileBtn.disabled = true;
            updateProfileBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang cập nhật...';
            
            // Collect form data
            const formData = new FormData(editProfileForm);
            const userId = document.getElementById('info-id').textContent;
            
            // Hàm gửi API khi đã xử lý xong avatar
            const sendUpdateRequest = (data) => {
                console.log('Đang gửi dữ liệu cập nhật:', data);
                
                // Call API to update profile
                fetch(`/api/users/${userId}/profile`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    console.log('Phản hồi API:', response.status);
                    if (!response.ok) {
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || 'Lỗi khi cập nhật thông tin cá nhân');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Dữ liệu phản hồi:', data);
                    if (data.success) {
                        // Update UI with new data
                        updateProfileUI(data.data);
                        
                        // Close the modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editProfileModal'));
                        modal.hide();
                        
                        // Show success message
                        showAlert('Cập nhật thông tin cá nhân thành công!', 'success');
                    } else {
                        throw new Error(data.message || 'Lỗi không xác định');
                    }
                })
                .catch(error => {
                    console.error('Error updating profile:', error);
                    showAlert('Đã xảy ra lỗi khi cập nhật thông tin: ' + error.message, 'danger');
                })
                .finally(() => {
                    // Re-enable the button
                    updateProfileBtn.disabled = false;
                    updateProfileBtn.innerHTML = '<i class="fas fa-save me-2"></i>Lưu thay đổi';
                });
            };
            
            // Convert FormData to JSON object
            const jsonData = {};
            formData.forEach((value, key) => {
                if (key !== 'avatar') {
                    jsonData[key] = value;
                }
            });
            
            // Kiểm tra xem có file avatar được chọn không
            const avatarFile = formData.get('avatar');
            if (avatarFile && avatarFile.size > 0) {
                // Xử lý file avatar bất đồng bộ
                const reader = new FileReader();
                reader.onload = function(e) {
                    jsonData.avatar = e.target.result;
                    // Sau khi đọc file xong, gửi yêu cầu API
                    sendUpdateRequest(jsonData);
                };
                reader.onerror = function() {
                    showAlert('Không thể đọc file hình ảnh. Vui lòng thử lại với ảnh khác.', 'danger');
                    updateProfileBtn.disabled = false;
                    updateProfileBtn.innerHTML = '<i class="fas fa-save me-2"></i>Lưu thay đổi';
                };
                reader.readAsDataURL(avatarFile);
            } else {
                // Không có file avatar, gửi yêu cầu API ngay
                sendUpdateRequest(jsonData);
            }
        });
        
        // Handle password change form submission
        const changePasswordForm = document.getElementById('change-password-form');
        const changePasswordBtn = document.getElementById('change-password-btn');
        
        changePasswordForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Validate password confirmation
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('new_password_confirmation').value;
            
            if (newPassword !== confirmPassword) {
                showAlert('Mật khẩu xác nhận không khớp với mật khẩu mới', 'danger');
                return;
            }
            
            // Disable the button and show loading state
            changePasswordBtn.disabled = true;
            changePasswordBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang cập nhật...';
            
            // Collect form data
            const userId = document.getElementById('info-id').textContent;
            
            // Convert form data to JSON
            const jsonData = {
                currentPassword: document.getElementById('current_password').value,
                newPassword: newPassword
            };
            
            console.log('Đang gửi yêu cầu đổi mật khẩu:', { userId, containsPassword: !!jsonData.currentPassword });
            
            // Call API to change password
            fetch(`/api/users/${userId}/change-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(jsonData)
            })
            .then(response => {
                console.log('Phản hồi API đổi mật khẩu:', response.status);
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Có lỗi xảy ra');
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Dữ liệu phản hồi đổi mật khẩu:', data);
                if (data.success) {
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
                    modal.hide();
                    
                    // Reset form
                    changePasswordForm.reset();
                    
                    // Show success message
                    showAlert('Đổi mật khẩu thành công!', 'success');
                } else {
                    throw new Error(data.message || 'Lỗi không xác định');
                }
            })
            .catch(error => {
                console.error('Error changing password:', error);
                showAlert('Đã xảy ra lỗi: ' + error.message, 'danger');
            })
            .finally(() => {
                // Re-enable the button
                changePasswordBtn.disabled = false;
                changePasswordBtn.innerHTML = '<i class="fas fa-key me-2"></i>Cập nhật mật khẩu';
            });
        });
        
        // Load user data from API
        function loadUserData() {
            const userId = document.getElementById('info-id').textContent;
            
            fetch(`/api/users/${userId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Không thể tải thông tin người dùng');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    updateProfileUI(data.data);
                }
            })
            .catch(error => {
                console.error('Error loading user data:', error);
                showAlert('Không thể tải thông tin người dùng: ' + error.message, 'danger');
            });
        }
        
        // Tải thông tin người dùng khi trang được tải
        // loadUserData();
    });
</script>
@endpush
@endsection