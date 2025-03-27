@extends('layouts.app')

@section('title', 'Danh sách thuốc')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h1>Danh sách thuốc</h1>
        <p class="lead">Thông tin về các loại thuốc từ hệ thống</p>
    </div>
    @if(Session::has('user') && Session::get('user')['role'] == 'admin')
    <div class="col-md-4 text-end">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMedicineModal">
            <i class="fas fa-plus me-1"></i>Thêm thuốc mới
        </button>
        <button type="button" class="btn btn-info ms-2" data-bs-toggle="modal" data-bs-target="#importCSVModal">
            <i class="fas fa-file-import me-1"></i>Import CSV
        </button>
    </div>
    @endif
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <input type="text" id="searchInput" class="form-control mb-3" placeholder="Tìm kiếm thuốc...">
            </div>
        </div>
    </div>
</div>

<div class="row" id="medicines-container">
    @if(isset($medicines) && count($medicines) > 0)
        @foreach($medicines as $medicine)
        <div class="col-md-4 mb-4 medicine-card">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">{{ $medicine['ten_thuoc'] ?? 'Không có tên' }}</h5>
                </div>
                @if(isset($medicine['hinh_anh']) && !empty($medicine['hinh_anh']))
                    <img src="{{ $medicine['hinh_anh'] }}" class="card-img-top" alt="{{ $medicine['ten_thuoc'] }}" style="height: 200px; object-fit: cover;">
                @else
                    <div class="text-center pt-3 pb-3 bg-light">
                        <i class="fa fa-image fa-3x text-muted"></i>
                        <p class="mt-2">Không có hình ảnh</p>
                    </div>
                @endif
                <div class="card-body">
                    <p class="card-text"><strong>Công dụng:</strong> {{ Str::limit($medicine['cong_dung'] ?? 'N/A', 100) }}</p>
                    <p class="card-text"><strong>Nhà sản xuất:</strong> {{ $medicine['nha_san_xuat'] ?? 'N/A' }}</p>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong>Đánh giá:</strong></span>
                            <div>
                                <span class="badge bg-success">Tốt: {{ $medicine['danh_gia_tot'] ?? '0' }}</span>
                                <span class="badge bg-warning text-dark">TB: {{ $medicine['danh_gia_trung_binh'] ?? '0' }}</span>
                                <span class="badge bg-danger">Kém: {{ $medicine['danh_gia_kem'] ?? '0' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('medicines.show', $medicine['id']) }}" class="btn btn-primary">
                        <i class="fas fa-eye me-1"></i>Chi tiết
                    </a>
                    
                    @if(Session::has('user'))
                        @if(Session::get('user')['role'] == 'admin')
                        <div>
                            <button type="button" class="btn btn-warning edit-medicine" data-id="{{ $medicine['id'] }}">
                                <i class="fas fa-edit me-1"></i>Sửa
                            </button>
                            <button type="button" class="btn btn-danger ms-1 delete-medicine" data-id="{{ $medicine['id'] }}" 
                                    data-name="{{ $medicine['ten_thuoc'] ?? 'Thuốc này' }}">
                                <i class="fas fa-trash me-1"></i>Xóa
                            </button>
                        </div>
                        @else
                        <button type="button" class="btn btn-outline-primary save-favorite-medicine" data-id="{{ $medicine['id'] }}">
                            <i class="fas fa-bookmark me-1"></i>Lưu thuốc
                        </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    @else
        <div class="col-12">
            <div class="alert alert-info">
                Không có dữ liệu thuốc
            </div>
        </div>
    @endif
</div>

@if(isset($pagination) && $pagination['totalPages'] > 1)
<div class="row">
    <div class="col-12">
        <nav aria-label="Phân trang thuốc">
            <ul class="pagination pagination-circle justify-content-center">
                {{-- Nút về trang đầu --}}
                <li class="page-item {{ !$pagination['hasPreviousPage'] ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ route('medicines', ['page' => 1]) }}" aria-label="Trang đầu" title="Trang đầu">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
                
                {{-- Nút về trang trước --}}
                <li class="page-item {{ !$pagination['hasPreviousPage'] ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ route('medicines', ['page' => $pagination['currentPage'] - 1]) }}" aria-label="Trang trước" title="Trang trước">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>
                
                {{-- Hiển thị các nút trang --}}
                @php
                    $startPage = max(1, $pagination['currentPage'] - 2);
                    $endPage = min($pagination['totalPages'], $pagination['currentPage'] + 2);
                    
                    // Đảm bảo luôn hiển thị ít nhất 5 trang nếu có đủ
                    if ($endPage - $startPage + 1 < 5) {
                        if ($startPage == 1) {
                            $endPage = min($pagination['totalPages'], $startPage + 4);
                        } elseif ($endPage == $pagination['totalPages']) {
                            $startPage = max(1, $endPage - 4);
                        }
                    }
                @endphp
                
                {{-- Hiển thị "..." nếu không bắt đầu từ trang 1 --}}
                @if($startPage > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ route('medicines', ['page' => 1]) }}">1</a>
                    </li>
                    @if($startPage > 2)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                @endif
                
                {{-- Hiển thị các trang trong phạm vi đã tính --}}
                @for($i = $startPage; $i <= $endPage; $i++)
                    <li class="page-item {{ $pagination['currentPage'] == $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ route('medicines', ['page' => $i]) }}">{{ $i }}</a>
                    </li>
                @endfor
                
                {{-- Hiển thị "..." nếu không kết thúc ở trang cuối --}}
                @if($endPage < $pagination['totalPages'])
                    @if($endPage < $pagination['totalPages'] - 1)
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    @endif
                    <li class="page-item">
                        <a class="page-link" href="{{ route('medicines', ['page' => $pagination['totalPages']]) }}">{{ $pagination['totalPages'] }}</a>
                    </li>
                @endif
                
                {{-- Nút đến trang tiếp theo --}}
                <li class="page-item {{ !$pagination['hasNextPage'] ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ route('medicines', ['page' => $pagination['currentPage'] + 1]) }}" aria-label="Trang sau" title="Trang sau">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
                
                {{-- Nút đến trang cuối --}}
                <li class="page-item {{ !$pagination['hasNextPage'] ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ route('medicines', ['page' => $pagination['totalPages']]) }}" aria-label="Trang cuối" title="Trang cuối">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="text-center mt-2 mb-4">
            <span class="text-muted">
                Trang {{ $pagination['currentPage'] }} / {{ $pagination['totalPages'] }}
                ({{ count($medicines) }} trên tổng số {{ $pagination['totalItems'] }} thuốc)
            </span>
        </div>
    </div>
</div>
@endif

<!-- Modal thêm thuốc yêu thích -->
<div class="modal fade" id="addFavoriteMedicineModal" tabindex="-1" aria-labelledby="addFavoriteMedicineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addFavoriteMedicineModalLabel">Lưu thuốc vào danh sách yêu thích</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="saveFavoriteMedicineForm">
                    <input type="hidden" id="favorite_medicine_id" value="">
                    <div class="mb-3">
                        <label for="medicine_note" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="medicine_note" rows="3" placeholder="Nhập ghi chú về thuốc này (không bắt buộc)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="saveFavoriteMedicineBtn">Lưu</button>
            </div>
        </div>
    </div>
</div>

@if(Session::has('user') && Session::get('user')['role'] == 'admin')
<!-- Modal thêm thuốc mới -->
<div class="modal fade" id="addMedicineModal" tabindex="-1" aria-labelledby="addMedicineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addMedicineModalLabel">Thêm thuốc mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addMedicineForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ten_thuoc" class="form-label">Tên thuốc <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ten_thuoc" name="ten_thuoc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nha_san_xuat" class="form-label">Nhà sản xuất</label>
                            <input type="text" class="form-control" id="nha_san_xuat" name="nha_san_xuat">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="thanh_phan" class="form-label">Thành phần</label>
                        <textarea class="form-control" id="thanh_phan" name="thanh_phan" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cong_dung" class="form-label">Công dụng</label>
                        <textarea class="form-control" id="cong_dung" name="cong_dung" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tac_dung_phu" class="form-label">Tác dụng phụ</label>
                        <textarea class="form-control" id="tac_dung_phu" name="tac_dung_phu" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="hinh_anh" class="form-label">URL hình ảnh</label>
                        <input type="url" class="form-control" id="hinh_anh" name="hinh_anh" placeholder="https://example.com/image.jpg">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="danh_gia_tot" class="form-label">Đánh giá tốt</label>
                            <input type="number" class="form-control" id="danh_gia_tot" name="danh_gia_tot" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="danh_gia_trung_binh" class="form-label">Đánh giá trung bình</label>
                            <input type="number" class="form-control" id="danh_gia_trung_binh" name="danh_gia_trung_binh" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="danh_gia_kem" class="form-label">Đánh giá kém</label>
                            <input type="number" class="form-control" id="danh_gia_kem" name="danh_gia_kem" min="0" value="0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" id="saveMedicineBtn">Lưu thuốc</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal sửa thuốc -->
<div class="modal fade" id="editMedicineModal" tabindex="-1" aria-labelledby="editMedicineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editMedicineModalLabel">Sửa thông tin thuốc</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMedicineForm">
                    <input type="hidden" id="edit_medicine_id" name="edit_medicine_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_ten_thuoc" class="form-label">Tên thuốc <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_ten_thuoc" name="edit_ten_thuoc" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_nha_san_xuat" class="form-label">Nhà sản xuất</label>
                            <input type="text" class="form-control" id="edit_nha_san_xuat" name="edit_nha_san_xuat">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_thanh_phan" class="form-label">Thành phần</label>
                        <textarea class="form-control" id="edit_thanh_phan" name="edit_thanh_phan" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_cong_dung" class="form-label">Công dụng</label>
                        <textarea class="form-control" id="edit_cong_dung" name="edit_cong_dung" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_tac_dung_phu" class="form-label">Tác dụng phụ</label>
                        <textarea class="form-control" id="edit_tac_dung_phu" name="edit_tac_dung_phu" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_hinh_anh" class="form-label">URL hình ảnh</label>
                        <input type="url" class="form-control" id="edit_hinh_anh" name="edit_hinh_anh" placeholder="https://example.com/image.jpg">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_danh_gia_tot" class="form-label">Đánh giá tốt</label>
                            <input type="number" class="form-control" id="edit_danh_gia_tot" name="edit_danh_gia_tot" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_danh_gia_trung_binh" class="form-label">Đánh giá trung bình</label>
                            <input type="number" class="form-control" id="edit_danh_gia_trung_binh" name="edit_danh_gia_trung_binh" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_danh_gia_kem" class="form-label">Đánh giá kém</label>
                            <input type="number" class="form-control" id="edit_danh_gia_kem" name="edit_danh_gia_kem" min="0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-warning" id="updateMedicineBtn">Cập nhật</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal import CSV -->
<div class="modal fade" id="importCSVModal" tabindex="-1" aria-labelledby="importCSVModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="importCSVModalLabel">Import danh sách thuốc từ CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importCSVForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Chọn file CSV <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i> File CSV phải có các cột: ten_thuoc, thanh_phan, cong_dung, tac_dung_phu, hinh_anh, nha_san_xuat
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-info" id="importCSVBtn">Import</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận xóa thuốc -->
<div class="modal fade" id="confirmDeleteMedicineModal" tabindex="-1" aria-labelledby="confirmDeleteMedicineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteMedicineModalLabel">Xác nhận xóa thuốc</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="delete_medicine_id" value="">
                <p class="mb-0">Bạn có chắc chắn muốn xóa thuốc "<span id="delete_medicine_name" class="fw-bold"></span>" không?</p>
                <p class="text-danger mb-0 mt-2"><i class="fas fa-exclamation-triangle me-1"></i>Lưu ý: Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Hủy
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteMedicineBtn">
                    <i class="fas fa-trash me-1"></i>Xóa thuốc
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const medicinesContainer = document.getElementById('medicines-container');
    const paginationSection = document.querySelector('.pagination')?.closest('.row');
    
    // Modal yêu thích thuốc
    const favoriteModal = new bootstrap.Modal(document.getElementById('addFavoriteMedicineModal'), {backdrop: 'static'});
    const saveFavoriteMedicineBtn = document.getElementById('saveFavoriteMedicineBtn');
    let currentFavoriteBtn = null;
    
    // Thêm chức năng debounce để tránh gọi API quá nhiều
    let searchTimeout;
    
    searchInput.addEventListener('keyup', function() {
        clearTimeout(searchTimeout);
        const searchValue = this.value.trim();
        
        // Nếu ô tìm kiếm trống, hiển thị lại trang mặc định
        if (searchValue === '') {
            location.reload();
            return;
        }
        
        searchTimeout = setTimeout(() => {
            // Hiển thị loading
            medicinesContainer.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tìm kiếm...</span></div></div>';
            
            // Ẩn phân trang khi đang tìm kiếm
            if (paginationSection) {
                paginationSection.style.display = 'none';
            }
            
            // Gọi API tìm kiếm (tìm kiếm toàn bộ dữ liệu)
            fetch(`http://localhost:3000/api/medicines/search?searchTerm=${encodeURIComponent(searchValue)}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    // Hiển thị kết quả tìm kiếm
                    medicinesContainer.innerHTML = '';
                    data.data.forEach(medicine => {
                        const medicineCard = createMedicineCard(medicine);
                        medicinesContainer.appendChild(medicineCard);
                    });
                    
                    // Hiển thị thông tin tìm kiếm
                    medicinesContainer.insertAdjacentHTML('beforeend', 
                        `<div class="col-12 mt-3">
                            <div class="alert alert-success">
                                Tìm thấy ${data.data.length} kết quả cho "${searchValue}"
                                <button class="btn btn-sm btn-outline-primary float-end" onclick="location.reload()">
                                    <i class="fas fa-sync-alt me-1"></i>Xem tất cả
                                </button>
                            </div>
                        </div>`
                    );
                } else {
                    medicinesContainer.innerHTML = `
                        <div class="col-12">
                            <div class="alert alert-info">
                                Không tìm thấy thuốc phù hợp với từ khóa "${searchValue}"
                                <button class="btn btn-sm btn-outline-primary float-end" onclick="location.reload()">
                                    <i class="fas fa-sync-alt me-1"></i>Xem tất cả
                                </button>
                            </div>
                        </div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                medicinesContainer.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger">
                            Đã xảy ra lỗi khi tìm kiếm. Vui lòng thử lại sau.
                            <button class="btn btn-sm btn-outline-primary float-end" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-1"></i>Xem tất cả
                            </button>
                        </div>
                    </div>`;
            });
        }, 500); // Đợi 500ms sau khi người dùng ngừng gõ
    });
    
    // Hàm tạo card cho mỗi thuốc
    function createMedicineCard(medicine) {
        const col = document.createElement('div');
        col.className = 'col-md-4 mb-4 medicine-card';
        
        let imageHtml = '';
        if (medicine.hinh_anh && medicine.hinh_anh.trim() !== '') {
            imageHtml = `<img src="${medicine.hinh_anh}" class="card-img-top" alt="${medicine.ten_thuoc}" style="height: 200px; object-fit: cover;">`;
        } else {
            imageHtml = `
                <div class="text-center pt-3 pb-3 bg-light">
                    <i class="fa fa-image fa-3x text-muted"></i>
                    <p class="mt-2">Không có hình ảnh</p>
                </div>
            `;
        }
        
        col.innerHTML = `
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">${medicine.ten_thuoc || 'Không có tên'}</h5>
                </div>
                ${imageHtml}
                <div class="card-body">
                    <p class="card-text"><strong>Công dụng:</strong> ${(medicine.cong_dung || 'N/A').substring(0, 100)}${medicine.cong_dung && medicine.cong_dung.length > 100 ? '...' : ''}</p>
                    <p class="card-text"><strong>Nhà sản xuất:</strong> ${medicine.nha_san_xuat || 'N/A'}</p>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong>Đánh giá:</strong></span>
                            <div>
                                <span class="badge bg-success">Tốt: ${medicine.danh_gia_tot || '0'}</span>
                                <span class="badge bg-warning text-dark">TB: ${medicine.danh_gia_trung_binh || '0'}</span>
                                <span class="badge bg-danger">Kém: ${medicine.danh_gia_kem || '0'}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/medicines/${medicine.id}" class="btn btn-primary">
                        <i class="fas fa-eye me-1"></i>Chi tiết
                    </a>
                    
                    @if(Session::has('user'))
                        @if(Session::get('user')['role'] == 'admin')
                        <div>
                            <button type="button" class="btn btn-warning edit-medicine" data-id="${medicine.id}">
                                <i class="fas fa-edit me-1"></i>Sửa
                            </button>
                            <button type="button" class="btn btn-danger ms-1 delete-medicine" data-id="${medicine.id}" 
                                    data-name="${medicine.ten_thuoc || 'Thuốc này'}">
                                <i class="fas fa-trash me-1"></i>Xóa
                            </button>
                        </div>
                        @else
                        <button type="button" class="btn btn-outline-primary save-favorite-medicine" data-id="${medicine.id}">
                            <i class="fas fa-bookmark me-1"></i>Lưu thuốc
                        </button>
                        @endif
                    @endif
                </div>
            </div>
        `;
        
        return col;
    }

    // Xử lý sự kiện lưu thuốc vào danh sách yêu thích
    const saveFavoriteBtns = document.querySelectorAll('.save-favorite-medicine');
    
    saveFavoriteBtns.forEach(button => {
        button.addEventListener('click', function() {
            const medicineId = this.getAttribute('data-id');
            currentFavoriteBtn = this;
            
            // Hiển thị modal để nhập ghi chú
            document.getElementById('favorite_medicine_id').value = medicineId;
            document.getElementById('medicine_note').value = '';
            favoriteModal.show();
        });
    });
    
    // Xử lý sự kiện lưu thuốc yêu thích từ modal
    saveFavoriteMedicineBtn.addEventListener('click', function() {
        const medicineId = document.getElementById('favorite_medicine_id').value;
        const note = document.getElementById('medicine_note').value.trim();
        
        // Đóng modal
        favoriteModal.hide();
        
        if (!currentFavoriteBtn) return;
        
        // Hiển thị trạng thái đang xử lý
        const originalContent = currentFavoriteBtn.innerHTML;
        currentFavoriteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang lưu...';
        currentFavoriteBtn.disabled = true;
        
        // Gửi yêu cầu thêm vào danh sách yêu thích
        fetch('{{ route("add-favorite-medicine") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                medicine_id: medicineId,
                note: note
            })
        })
        .then(response => {
            console.log('Phản hồi ban đầu:', response);
            return response.json();
        })
        .then(data => {
            console.log('Dữ liệu phản hồi:', data);
            if (data.success) {
                // Cập nhật giao diện để hiển thị đã lưu
                currentFavoriteBtn.innerHTML = '<i class="fas fa-check me-1"></i>Đã lưu';
                currentFavoriteBtn.classList.remove('btn-outline-primary');
                currentFavoriteBtn.classList.add('btn-success');
                currentFavoriteBtn.disabled = true;
                
                // Hiển thị toast thông báo thành công
                showToast('Đã lưu thuốc vào danh sách yêu thích!', 'success');
            } else {
                // Khôi phục trạng thái nút
                currentFavoriteBtn.innerHTML = originalContent;
                currentFavoriteBtn.disabled = false;
                
                // Hiển thị toast thông báo lỗi
                showToast(data.message || 'Không thể thêm vào yêu thích!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Khôi phục trạng thái nút
            currentFavoriteBtn.innerHTML = originalContent;
            currentFavoriteBtn.disabled = false;
            
            // Hiển thị toast thông báo lỗi
            showToast('Đã xảy ra lỗi khi lưu thuốc! Vui lòng thử lại sau.', 'error');
        });
    });
    
    // Hàm hiển thị thông báo toast
    function showToast(message, type = 'info') {
        // Kiểm tra xem container toast đã tồn tại chưa
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        // Tạo toast mới
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.className = `toast ${type === 'error' ? 'bg-danger' : type === 'success' ? 'bg-success' : 'bg-info'} text-white`;
        toast.setAttribute('id', toastId);
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="toast-header bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} text-white">
                <strong class="me-auto">Thông báo</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Hiển thị toast
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 3000
        });
        bsToast.show();
        
        // Xóa toast sau khi ẩn
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }

    @if(Session::has('user') && Session::get('user')['role'] == 'admin')
    // Các hàm xử lý thêm, sửa, xóa thuốc cho admin
    
    // Thêm thuốc mới
    document.getElementById('saveMedicineBtn').addEventListener('click', function() {
        const formData = {
            ten_thuoc: document.getElementById('ten_thuoc').value,
            thanh_phan: document.getElementById('thanh_phan').value,
            cong_dung: document.getElementById('cong_dung').value,
            tac_dung_phu: document.getElementById('tac_dung_phu').value,
            hinh_anh: document.getElementById('hinh_anh').value,
            nha_san_xuat: document.getElementById('nha_san_xuat').value,
            danh_gia_tot: document.getElementById('danh_gia_tot').value || 0,
            danh_gia_trung_binh: document.getElementById('danh_gia_trung_binh').value || 0,
            danh_gia_kem: document.getElementById('danh_gia_kem').value || 0
        };

        if (!formData.ten_thuoc) {
            alert('Vui lòng nhập tên thuốc');
            return;
        }

        // Gửi request API
        fetch('/api/medicines/add', {
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
                // Đóng modal
                var modal = bootstrap.Modal.getInstance(document.getElementById('addMedicineModal'));
                modal.hide();
                
                // Thông báo thành công và làm mới trang
                alert('Thêm thuốc thành công!');
                location.reload();
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể thêm thuốc'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi thêm thuốc!');
        });
    });

    // Xử lý sự kiện nhấn nút sửa thuốc
    document.querySelectorAll('.edit-medicine').forEach(button => {
        button.addEventListener('click', function() {
            const medicineId = this.getAttribute('data-id');
            
            // Hiển thị trạng thái loading
            const card = this.closest('.card');
            card.style.opacity = '0.7';
            
            // Lấy dữ liệu thuốc
            fetch('/api/medicines/' + medicineId, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                card.style.opacity = '1';
                
                if (data.success) {
                    const medicine = data.data;
                    
                    // Điền dữ liệu vào form
                    document.getElementById('edit_medicine_id').value = medicine.id;
                    document.getElementById('edit_ten_thuoc').value = medicine.ten_thuoc || '';
                    document.getElementById('edit_thanh_phan').value = medicine.thanh_phan || '';
                    document.getElementById('edit_cong_dung').value = medicine.cong_dung || '';
                    document.getElementById('edit_tac_dung_phu').value = medicine.tac_dung_phu || '';
                    document.getElementById('edit_hinh_anh').value = medicine.hinh_anh || '';
                    document.getElementById('edit_nha_san_xuat').value = medicine.nha_san_xuat || '';
                    document.getElementById('edit_danh_gia_tot').value = medicine.danh_gia_tot || 0;
                    document.getElementById('edit_danh_gia_trung_binh').value = medicine.danh_gia_trung_binh || 0;
                    document.getElementById('edit_danh_gia_kem').value = medicine.danh_gia_kem || 0;
                    
                    // Hiển thị modal
                    var modal = new bootstrap.Modal(document.getElementById('editMedicineModal'));
                    modal.show();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể lấy thông tin thuốc'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                card.style.opacity = '1';
                alert('Đã xảy ra lỗi khi lấy thông tin thuốc!');
            });
        });
    });

    // Cập nhật thông tin thuốc
    document.getElementById('updateMedicineBtn').addEventListener('click', function() {
        const medicineId = document.getElementById('edit_medicine_id').value;
        const formData = {
            id: medicineId,
            ten_thuoc: document.getElementById('edit_ten_thuoc').value,
            thanh_phan: document.getElementById('edit_thanh_phan').value,
            cong_dung: document.getElementById('edit_cong_dung').value,
            tac_dung_phu: document.getElementById('edit_tac_dung_phu').value,
            hinh_anh: document.getElementById('edit_hinh_anh').value,
            nha_san_xuat: document.getElementById('edit_nha_san_xuat').value,
            danh_gia_tot: document.getElementById('edit_danh_gia_tot').value || 0,
            danh_gia_trung_binh: document.getElementById('edit_danh_gia_trung_binh').value || 0,
            danh_gia_kem: document.getElementById('edit_danh_gia_kem').value || 0
        };

        if (!formData.ten_thuoc) {
            alert('Vui lòng nhập tên thuốc');
            return;
        }

        // Gửi request API
        fetch('/api/medicines/update', {
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
                // Đóng modal
                var modal = bootstrap.Modal.getInstance(document.getElementById('editMedicineModal'));
                modal.hide();
                
                // Thông báo thành công và làm mới trang
                alert('Cập nhật thuốc thành công!');
                location.reload();
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể cập nhật thuốc'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi cập nhật thuốc!');
        });
    });

    // Xử lý sự kiện xóa thuốc
    document.querySelectorAll('.delete-medicine').forEach(button => {
        button.addEventListener('click', function() {
            const medicineId = this.getAttribute('data-id');
            const medicineName = this.getAttribute('data-name');
            
            // Cập nhật thông tin vào modal xác nhận
            document.getElementById('delete_medicine_id').value = medicineId;
            document.getElementById('delete_medicine_name').textContent = medicineName;
            
            // Hiển thị modal xác nhận
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteMedicineModal'));
            confirmModal.show();
        });
    });
    
    // Xử lý sự kiện xác nhận xóa thuốc
    document.getElementById('confirmDeleteMedicineBtn').addEventListener('click', function() {
        const medicineId = document.getElementById('delete_medicine_id').value;
        const button = document.querySelector(`.delete-medicine[data-id="${medicineId}"]`);
        const card = button.closest('.card');
        
        // Hiển thị trạng thái đang xử lý
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang xóa...';
        card.style.opacity = '0.7';
        
        // Gửi request xóa thuốc
        fetch('/api/medicines/delete', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id: medicineId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Xóa card khỏi giao diện
                const cardCol = card.closest('.col-md-4');
                cardCol.remove();
                
                // Đóng modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteMedicineModal'));
                modal.hide();
                
                // Hiển thị toast thông báo thành công
                showToast('Đã xóa thuốc thành công!', 'success');
                
                // Kiểm tra nếu không còn thuốc nào
                if (document.querySelectorAll('.medicine-card').length === 0) {
                    document.getElementById('medicines-container').innerHTML = `
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Không có dữ liệu thuốc
                            </div>
                        </div>
                    `;
                }
            } else {
                // Khôi phục trạng thái
                card.style.opacity = '1';
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-trash me-1"></i>Xóa thuốc';
                
                // Hiển thị toast thông báo lỗi
                showToast(data.message || 'Không thể xóa thuốc!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Khôi phục trạng thái
            card.style.opacity = '1';
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-trash me-1"></i>Xóa thuốc';
            
            // Hiển thị toast thông báo lỗi
            showToast('Đã xảy ra lỗi khi xóa thuốc! Vui lòng thử lại sau.', 'error');
        });
    });

    // Import CSV
    document.getElementById('importCSVBtn').addEventListener('click', function() {
        const fileInput = document.getElementById('csv_file');
        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Vui lòng chọn file CSV');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        // Hiển thị thông báo đang xử lý
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang xử lý...';

        // Gửi request API
        fetch('/api/medicines/import', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Reset trạng thái nút
            this.disabled = false;
            this.innerHTML = 'Import';
            
            if (data.success) {
                // Đóng modal
                var modal = bootstrap.Modal.getInstance(document.getElementById('importCSVModal'));
                modal.hide();
                
                // Thông báo thành công và làm mới trang
                alert(`Import thành công! Đã thêm ${data.data.length} thuốc.`);
                location.reload();
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể import file CSV'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.disabled = false;
            this.innerHTML = 'Import';
            alert('Đã xảy ra lỗi khi import file CSV!');
        });
    });
    @endif
});
</script>
@endsection