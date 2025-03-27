@extends('layouts.app')

@section('title', 'Quản lý người dùng')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h1>Quản lý người dùng</h1>
        <p class="lead">Thông tin người dùng trong hệ thống</p>
    </div>
    <div class="col-md-4 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-1"></i>Thêm người dùng mới
        </button>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <input type="text" id="searchInput" class="form-control mb-3" placeholder="Tìm kiếm người dùng...">
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên người dùng</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <!-- Dữ liệu người dùng sẽ được thêm vào đây -->
                        </tbody>
                    </table>
                </div>
                
                <div id="paginationContainer" class="mt-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span id="paginationInfo">Đang tải...</span>
                    </div>
                    <nav>
                        <ul id="pagination" class="pagination pagination-circle">
                            <!-- Phân trang sẽ được thêm vào đây -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm người dùng mới -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addUserModalLabel">Thêm người dùng mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label for="add_username" class="form-label">Tên người dùng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="add_email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="add_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_role" class="form-label">Vai trò</label>
                        <select class="form-select" id="add_role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_avatar" class="form-label">URL Avatar</label>
                        <input type="url" class="form-control" id="add_avatar" placeholder="https://example.com/avatar.jpg">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="saveUserBtn">Lưu người dùng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal sửa thông tin người dùng -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editUserModalLabel">Sửa thông tin người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id">
                    
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Tên người dùng <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="edit_email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Mật khẩu (để trống nếu không thay đổi)</label>
                        <input type="password" class="form-control" id="edit_password">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Vai trò</label>
                        <select class="form-select" id="edit_role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_avatar" class="form-label">URL Avatar</label>
                        <input type="url" class="form-control" id="edit_avatar">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="updateUserBtn">Cập nhật</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem chi tiết người dùng -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewUserModalLabel">Chi tiết người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img id="userAvatar" src="" alt="Avatar" class="rounded-circle" style="width: 100px; height: 100px;">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Tên người dùng</label>
                    <input type="text" class="form-control" id="viewUsername" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="viewEmail" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Vai trò</label>
                    <input type="text" class="form-control" id="viewRole" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Ngày tạo</label>
                    <input type="text" class="form-control" id="viewCreatedAt" readonly>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label class="form-label">Số thuốc đã lưu</label>
                            <input type="text" class="form-control" id="viewSavedMedicines" readonly>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label class="form-label">Số bệnh đã lưu</label>
                            <input type="text" class="form-control" id="viewSavedDiseases" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let usersData = [];
    
    // Hàm tải danh sách người dùng
    function loadUsers(page = 1) {
        // Hiển thị trạng thái đang tải
        document.getElementById('usersTableBody').innerHTML = '<tr><td colspan="6" class="text-center">Đang tải dữ liệu...</td></tr>';
        
        fetch(`/api/users?page=${page}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Lưu dữ liệu người dùng
                    usersData = data.data;
                    
                    // Hiển thị dữ liệu
                    displayUsers(usersData);
                    
                    // Hiển thị phân trang
                    displayPagination(data.pagination);
                } else {
                    document.getElementById('usersTableBody').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi: ' + (data.message || 'Không thể tải dữ liệu') + '</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('usersTableBody').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Đã xảy ra lỗi khi tải dữ liệu</td></tr>';
            });
    }
    
    // Hàm hiển thị danh sách người dùng
    function displayUsers(users) {
        const tableBody = document.getElementById('usersTableBody');
        tableBody.innerHTML = '';
        
        if (users.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Không có dữ liệu người dùng</td></tr>';
            return;
        }
        
        users.forEach(user => {
            const row = document.createElement('tr');
            
            // Định dạng ngày tạo
            const createdDate = new Date(user.created_at);
            const formattedDate = createdDate.toLocaleDateString('vi-VN');
            
            row.innerHTML = `
                <td>${user.id}</td>
                <td>${user.username || 'N/A'}</td>
                <td>${user.email || 'N/A'}</td>
                <td><span class="badge ${user.role === 'admin' ? 'bg-danger' : 'bg-success'}">${user.role || 'user'}</span></td>
                <td>${formattedDate}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-info view-user" data-id="${user.id}">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-warning edit-user" data-id="${user.id}">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger delete-user" data-id="${user.id}" data-name="${user.username}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            tableBody.appendChild(row);
        });
        
        // Thêm sự kiện cho các nút
        document.querySelectorAll('.view-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                viewUserDetails(userId);
            });
        });
        
        document.querySelectorAll('.edit-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                editUser(userId);
            });
        });
        
        document.querySelectorAll('.delete-user').forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                const userName = this.getAttribute('data-name');
                deleteUser(userId, userName);
            });
        });
    }
    
    // Hàm hiển thị phân trang
    function displayPagination(pagination) {
        if (!pagination) return;
        
        const paginationContainer = document.getElementById('pagination');
        const paginationInfo = document.getElementById('paginationInfo');
        
        // Cập nhật thông tin phân trang
        paginationInfo.textContent = `Hiển thị ${pagination.from || 0} - ${pagination.to || 0} trên tổng số ${pagination.total || 0} người dùng`;
        
        // Tạo các nút phân trang
        paginationContainer.innerHTML = '';
        
        // Nút Previous
        const prevBtn = document.createElement('li');
        prevBtn.className = `page-item ${pagination.current_page <= 1 ? 'disabled' : ''}`;
        prevBtn.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (pagination.current_page > 1) {
                loadUsers(pagination.current_page - 1);
            }
        });
        paginationContainer.appendChild(prevBtn);
        
        // Hiển thị các số trang
        const startPage = Math.max(1, pagination.current_page - 2);
        const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('li');
            pageBtn.className = `page-item ${pagination.current_page === i ? 'active' : ''}`;
            pageBtn.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            pageBtn.addEventListener('click', function(e) {
                e.preventDefault();
                loadUsers(i);
            });
            paginationContainer.appendChild(pageBtn);
        }
        
        // Nút Next
        const nextBtn = document.createElement('li');
        nextBtn.className = `page-item ${pagination.current_page >= pagination.last_page ? 'disabled' : ''}`;
        nextBtn.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (pagination.current_page < pagination.last_page) {
                loadUsers(pagination.current_page + 1);
            }
        });
        paginationContainer.appendChild(nextBtn);
    }
    
    // Hàm xem chi tiết người dùng
    function viewUserDetails(userId) {
        // Tìm thông tin người dùng từ dữ liệu đã tải
        const user = usersData.find(u => u.id == userId);
        
        if (user) {
            // Hiển thị avatar mặc định hoặc avatar người dùng
            document.getElementById('userAvatar').src = user.avatar || 'https://via.placeholder.com/100';
            
            // Hiển thị thông tin chi tiết
            document.getElementById('viewUsername').value = user.username || 'N/A';
            document.getElementById('viewEmail').value = user.email || 'N/A';
            document.getElementById('viewRole').value = user.role || 'user';
            
            // Định dạng ngày tạo
            const createdDate = new Date(user.created_at);
            document.getElementById('viewCreatedAt').value = createdDate.toLocaleDateString('vi-VN');
            
            // Hiển thị số lượng thuốc và bệnh đã lưu
            document.getElementById('viewSavedMedicines').value = user.saved_medicines || '0';
            document.getElementById('viewSavedDiseases').value = user.saved_diseases || '0';
            
            // Hiển thị modal
            const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
            modal.show();
        } else {
            alert('Không tìm thấy thông tin người dùng');
        }
    }
    
    // Hàm mở modal sửa thông tin người dùng
    function editUser(userId) {
        // Tìm thông tin người dùng từ dữ liệu đã tải
        const user = usersData.find(u => u.id == userId);
        
        if (user) {
            // Điền thông tin người dùng vào form
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username || '';
            document.getElementById('edit_email').value = user.email || '';
            document.getElementById('edit_password').value = ''; // Không hiển thị mật khẩu
            document.getElementById('edit_role').value = user.role || 'user';
            document.getElementById('edit_avatar').value = user.avatar || '';
            
            // Hiển thị modal
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        } else {
            alert('Không tìm thấy thông tin người dùng');
        }
    }
    
    // Hàm xóa người dùng
    function deleteUser(userId, userName) {
        if (confirm(`Bạn có chắc chắn muốn xóa người dùng "${userName || 'ID: ' + userId}" không? Hành động này không thể hoàn tác.`)) {
            fetch('/api/users/delete', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Xóa người dùng thành công!');
                    // Tải lại danh sách người dùng
                    loadUsers(currentPage);
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể xóa người dùng'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi xóa người dùng!');
            });
        }
    }
    
    // Xử lý sự kiện thêm người dùng mới
    document.getElementById('saveUserBtn').addEventListener('click', function() {
        // Lấy dữ liệu từ form
        const formData = {
            username: document.getElementById('add_username').value,
            email: document.getElementById('add_email').value,
            password: document.getElementById('add_password').value,
            role: document.getElementById('add_role').value,
            avatar: document.getElementById('add_avatar').value
        };
        
        // Kiểm tra dữ liệu
        if (!formData.username || !formData.email || !formData.password) {
            alert('Vui lòng nhập đầy đủ thông tin bắt buộc (Tên người dùng, Email, Mật khẩu)');
            return;
        }
        
        // Gửi request thêm người dùng
        fetch('/api/users/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Thêm người dùng thành công!');
                
                // Đóng modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                modal.hide();
                
                // Reset form
                document.getElementById('addUserForm').reset();
                
                // Tải lại danh sách người dùng
                loadUsers(currentPage);
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể thêm người dùng'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi thêm người dùng!');
        });
    });
    
    // Xử lý sự kiện cập nhật thông tin người dùng
    document.getElementById('updateUserBtn').addEventListener('click', function() {
        // Lấy dữ liệu từ form
        const formData = {
            id: document.getElementById('edit_user_id').value,
            username: document.getElementById('edit_username').value,
            email: document.getElementById('edit_email').value,
            role: document.getElementById('edit_role').value,
            avatar: document.getElementById('edit_avatar').value
        };
        
        // Thêm mật khẩu nếu có
        const password = document.getElementById('edit_password').value;
        if (password) {
            formData.password = password;
        }
        
        // Kiểm tra dữ liệu
        if (!formData.username || !formData.email) {
            alert('Vui lòng nhập đầy đủ thông tin bắt buộc (Tên người dùng, Email)');
            return;
        }
        
        // Gửi request cập nhật người dùng
        fetch('/api/users/update', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cập nhật thông tin người dùng thành công!');
                
                // Đóng modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                modal.hide();
                
                // Tải lại danh sách người dùng
                loadUsers(currentPage);
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể cập nhật thông tin người dùng'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi cập nhật thông tin người dùng!');
        });
    });
    
    // Tìm kiếm người dùng
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        
        if (usersData.length > 0) {
            const filteredUsers = usersData.filter(user => {
                return (
                    (user.username && user.username.toLowerCase().includes(searchValue)) ||
                    (user.email && user.email.toLowerCase().includes(searchValue))
                );
            });
            
            displayUsers(filteredUsers);
        }
    });
    
    // Tải danh sách người dùng khi trang được tải
    loadUsers();
});
</script>
@endsection 