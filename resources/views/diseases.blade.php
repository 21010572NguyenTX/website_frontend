@extends('layouts.app')

@section('title', 'Danh sách bệnh')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h1>Danh sách bệnh</h1>
        <p class="lead">Thông tin về các loại bệnh từ hệ thống</p>
    </div>
    @if(Session::has('user') && Session::get('user')['role'] == 'admin')
    <div class="col-md-4 text-end">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDiseaseModal">
            <i class="fas fa-plus me-1"></i>Thêm bệnh mới
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
                <div class="position-relative">
                    <div class="input-group mb-3">
                        <input type="text" id="searchInput" class="form-control" placeholder="Tìm kiếm bệnh...">
                        <button class="btn btn-primary" type="button" id="searchButton">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                    <div id="searchResults" class="list-group position-absolute w-100 d-none shadow" style="z-index: 1000; max-height: 300px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="diseases-container">
    @if(isset($diseases) && count($diseases) > 0)
        @foreach($diseases as $disease)
        <div class="col-md-4 mb-4 disease-card">
            <div class="card h-100">
                <div class="card-header bg-rose text-white" style="background-color: #f8b4b9 !important;">
                    <h5 class="card-title mb-0">{{ $disease['ten_benh'] ?? 'Không có tên' }}</h5>
                </div>
                <div class="card-body">
                    <p class="card-text"><strong>Định nghĩa:</strong> {{ Str::limit($disease['dinh_nghia'] ?? 'N/A', 100) }}</p>
                    <p class="card-text"><strong>Triệu chứng:</strong> {{ Str::limit($disease['trieu_chung'] ?? 'N/A', 100) }}</p>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('diseases.show', $disease['id']) }}" class="btn btn-primary">
                        <i class="fas fa-eye me-1"></i>Chi tiết
                    </a>
                    
                    @if(Session::has('user'))
                        @if(Session::get('user')['role'] == 'admin')
                        <div>
                            <button type="button" class="btn btn-warning edit-disease" data-id="{{ $disease['id'] }}">
                                <i class="fas fa-edit me-1"></i>Sửa
                            </button>
                            <button type="button" class="btn ms-1 delete-disease" data-id="{{ $disease['id'] }}" 
                                    data-name="{{ $disease['ten_benh'] ?? 'Bệnh này' }}" style="background-color: #f8b4b9; color: white;">
                                <i class="fas fa-trash me-1"></i>Xóa
                            </button>
                        </div>
                        @else
                        <button type="button" class="btn btn-outline-primary save-favorite-disease" data-id="{{ $disease['id'] }}">
                            <i class="fas fa-bookmark me-1"></i>Lưu bệnh
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
                Không có dữ liệu bệnh
            </div>
        </div>
    @endif
</div>

@if(isset($pagination) && $pagination['totalPages'] > 1)
<div class="row">
    <div class="col-12">
        <nav aria-label="Phân trang bệnh">
            <ul class="pagination pagination-circle justify-content-center">
                {{-- Nút về trang đầu --}}
                <li class="page-item {{ !$pagination['hasPreviousPage'] ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ route('diseases', ['page' => 1]) }}" aria-label="Trang đầu" title="Trang đầu">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
                
                {{-- Nút về trang trước --}}
                <li class="page-item {{ !$pagination['hasPreviousPage'] ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ route('diseases', ['page' => $pagination['currentPage'] - 1]) }}" aria-label="Trang trước" title="Trang trước">
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
                        <a class="page-link" href="{{ route('diseases', ['page' => 1]) }}">1</a>
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
                        <a class="page-link" href="{{ route('diseases', ['page' => $i]) }}">{{ $i }}</a>
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
                        <a class="page-link" href="{{ route('diseases', ['page' => $pagination['totalPages']]) }}">{{ $pagination['totalPages'] }}</a>
                    </li>
                @endif
                
                {{-- Nút đến trang tiếp theo --}}
                <li class="page-item {{ !$pagination['hasNextPage'] ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ route('diseases', ['page' => $pagination['currentPage'] + 1]) }}" aria-label="Trang sau" title="Trang sau">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
                
                {{-- Nút đến trang cuối --}}
                <li class="page-item {{ !$pagination['hasNextPage'] ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ route('diseases', ['page' => $pagination['totalPages']]) }}" aria-label="Trang cuối" title="Trang cuối">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="text-center mt-2 mb-4">
            <span class="text-muted">
                Trang {{ $pagination['currentPage'] }} / {{ $pagination['totalPages'] }}
                ({{ count($diseases) }} trên tổng số {{ $pagination['totalItems'] }} bệnh)
            </span>
        </div>
    </div>
</div>
@endif

<!-- Modal thêm bệnh yêu thích -->
<div class="modal fade" id="addFavoriteDiseaseModal" tabindex="-1" aria-labelledby="addFavoriteDiseaseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-rose text-white" style="background-color: #f8b4b9 !important;">
                <h5 class="modal-title" id="addFavoriteDiseaseModalLabel">Lưu bệnh vào danh sách yêu thích</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="saveFavoriteDiseaseForm">
                    <input type="hidden" id="favorite_disease_id" value="">
                    <div class="mb-3">
                        <label for="disease_note" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="disease_note" rows="3" placeholder="Nhập ghi chú về bệnh này (không bắt buộc)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn" style="background-color: #f8b4b9; color: white;" id="saveFavoriteDiseaseBtn">Lưu</button>
            </div>
        </div>
    </div>
</div>

@if(Session::has('user') && Session::get('user')['role'] == 'admin')
<!-- Modal thêm bệnh mới -->
<div class="modal fade" id="addDiseaseModal" tabindex="-1" aria-labelledby="addDiseaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addDiseaseModalLabel">Thêm bệnh mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addDiseaseForm">
                    <div class="mb-3">
                        <label for="ten_benh" class="form-label">Tên bệnh <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ten_benh" name="ten_benh" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dinh_nghia" class="form-label">Định nghĩa</label>
                        <textarea class="form-control" id="dinh_nghia" name="dinh_nghia" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nguyen_nhan" class="form-label">Nguyên nhân</label>
                        <textarea class="form-control" id="nguyen_nhan" name="nguyen_nhan" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="trieu_chung" class="form-label">Triệu chứng</label>
                        <textarea class="form-control" id="trieu_chung" name="trieu_chung" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="chan_doan" class="form-label">Chẩn đoán</label>
                        <textarea class="form-control" id="chan_doan" name="chan_doan" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dieu_tri" class="form-label">Điều trị</label>
                        <textarea class="form-control" id="dieu_tri" name="dieu_tri" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" id="saveDiseaseBtn">Lưu bệnh</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal sửa bệnh -->
<div class="modal fade" id="editDiseaseModal" tabindex="-1" aria-labelledby="editDiseaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editDiseaseModalLabel">Sửa thông tin bệnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editDiseaseForm">
                    <input type="hidden" id="edit_disease_id" name="edit_disease_id">
                    <div class="mb-3">
                        <label for="edit_ten_benh" class="form-label">Tên bệnh <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_benh" name="edit_ten_benh" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_dinh_nghia" class="form-label">Định nghĩa</label>
                        <textarea class="form-control" id="edit_dinh_nghia" name="edit_dinh_nghia" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_nguyen_nhan" class="form-label">Nguyên nhân</label>
                        <textarea class="form-control" id="edit_nguyen_nhan" name="edit_nguyen_nhan" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_trieu_chung" class="form-label">Triệu chứng</label>
                        <textarea class="form-control" id="edit_trieu_chung" name="edit_trieu_chung" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_chan_doan" class="form-label">Chẩn đoán</label>
                        <textarea class="form-control" id="edit_chan_doan" name="edit_chan_doan" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_dieu_tri" class="form-label">Điều trị</label>
                        <textarea class="form-control" id="edit_dieu_tri" name="edit_dieu_tri" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-warning" id="updateDiseaseBtn">Cập nhật</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal import CSV -->
<div class="modal fade" id="importCSVModal" tabindex="-1" aria-labelledby="importCSVModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="importCSVModalLabel">Import danh sách bệnh từ CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importCSVForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Chọn file CSV <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-1"></i> File CSV phải có các cột: ten_benh, dinh_nghia, nguyen_nhan, trieu_chung, chan_doan, dieu_tri
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

<!-- Modal xác nhận xóa bệnh -->
<div class="modal fade" id="confirmDeleteDiseaseModal" tabindex="-1" aria-labelledby="confirmDeleteDiseaseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="confirmDeleteDiseaseModalLabel">Xác nhận xóa bệnh</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="delete_disease_id" value="">
                <p class="mb-0">Bạn có chắc chắn muốn xóa bệnh "<span id="delete_disease_name" class="fw-bold"></span>" không?</p>
                <p class="text-danger mb-0 mt-2"><i class="fas fa-exclamation-triangle me-1"></i>Lưu ý: Hành động này không thể hoàn tác!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Hủy
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteDiseaseBtn">
                    <i class="fas fa-trash me-1"></i>Xóa bệnh
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const searchResults = document.getElementById('searchResults');
    const diseasesContainer = document.getElementById('diseases-container');
    const paginationSection = document.querySelector('.pagination')?.closest('.row');
    
    let searchTimeout;
    
    // Modal yêu thích bệnh
    const favoriteModal = new bootstrap.Modal(document.getElementById('addFavoriteDiseaseModal'), {backdrop: 'static'});
    const saveFavoriteDiseaseBtn = document.getElementById('saveFavoriteDiseaseBtn');
    let currentFavoriteBtn = null;
    
    // Sự kiện nhập liệu vào ô tìm kiếm - hiển thị gợi ý
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchValue = this.value.trim();
        
        // Nếu ô tìm kiếm trống, ẩn kết quả gợi ý
        if (searchValue === '') {
            searchResults.classList.add('d-none');
            searchResults.innerHTML = '';
            return;
        }
        
        // Hiển thị trạng thái "đang tải..."
        searchResults.classList.remove('d-none');
        searchResults.innerHTML = '<div class="list-group-item text-center"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Đang tìm kiếm...</div>';
        
        // Đợi 300ms sau khi người dùng ngừng gõ rồi mới gọi API
        searchTimeout = setTimeout(() => {
            // Gọi API tìm kiếm
            fetch(`http://localhost:3000/api/diseases/search?searchTerm=${encodeURIComponent(searchValue)}`)
                .then(response => response.json())
                .then(data => {
                    // Xóa kết quả cũ
                    searchResults.innerHTML = '';
                    
                    if (data.success && data.data && data.data.length > 0) {
                        // Hiển thị kết quả gợi ý
                        searchResults.classList.remove('d-none');
                        
                        // Giới hạn số lượng gợi ý hiển thị
                        const maxSuggestions = Math.min(data.data.length, 10);
                        
                        for (let i = 0; i < maxSuggestions; i++) {
                            const disease = data.data[i];
                            const item = document.createElement('a');
                            item.className = 'list-group-item list-group-item-action';
                            item.href = `/diseases/${disease.id}`; // Đường dẫn đến trang chi tiết
                            item.innerHTML = `
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-danger">${highlightMatch(disease.ten_benh, searchValue)}</strong>
                                        <div class="small text-muted">${truncateText(disease.dinh_nghia || 'Không có mô tả', 80)}</div>
                                    </div>
                                    <span class="badge bg-primary rounded-pill"><i class="fas fa-eye"></i></span>
                                </div>
                            `;
                            searchResults.appendChild(item);
                        }
                        
                        // Nếu có nhiều kết quả hơn, hiển thị nút "Xem thêm"
                        if (data.data.length > maxSuggestions) {
                            const viewMoreItem = document.createElement('a');
                            viewMoreItem.className = 'list-group-item list-group-item-action text-center';
                            viewMoreItem.href = '#';
                            viewMoreItem.innerHTML = `Xem thêm ${data.data.length - maxSuggestions} kết quả khác...`;
                            viewMoreItem.addEventListener('click', function(e) {
                                e.preventDefault();
                                performSearch();
                                searchResults.classList.add('d-none');
                            });
                            searchResults.appendChild(viewMoreItem);
                        }
                    } else {
                        // Hiển thị thông báo không tìm thấy
                        searchResults.innerHTML = '<div class="list-group-item text-center text-muted">Không tìm thấy kết quả phù hợp</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    searchResults.innerHTML = '<div class="list-group-item text-center text-danger">Đã xảy ra lỗi khi tìm kiếm</div>';
                });
        }, 300);
    });
    
    // Ẩn kết quả gợi ý khi click ra ngoài
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('d-none');
        }
    });
    
    // Sự kiện khi nhấn nút tìm kiếm
    searchButton.addEventListener('click', performSearch);
    
    // Sự kiện khi nhấn Enter trong ô tìm kiếm
    searchInput.addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            performSearch();
            searchResults.classList.add('d-none');
        }
    });
    
    // Hàm tìm kiếm đầy đủ (như trước đây)
    function performSearch() {
        const searchValue = searchInput.value.trim();
        
        // Nếu ô tìm kiếm trống, hiển thị lại trang mặc định
        if (searchValue === '') {
            location.reload();
            return;
        }
        
        // Hiển thị loading
        diseasesContainer.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tìm kiếm...</span></div></div>';
        
        // Ẩn phân trang khi đang tìm kiếm
        if (paginationSection) {
            paginationSection.style.display = 'none';
        }
        
        // Gọi API tìm kiếm (tìm kiếm toàn bộ dữ liệu)
        fetch(`http://localhost:3000/api/diseases/search?searchTerm=${encodeURIComponent(searchValue)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    // Hiển thị kết quả tìm kiếm
                    diseasesContainer.innerHTML = '';
                    data.data.forEach(disease => {
                        const diseaseCard = createDiseaseCard(disease);
                        diseasesContainer.appendChild(diseaseCard);
                    });
                    
                    // Hiển thị thông tin tìm kiếm
                    diseasesContainer.insertAdjacentHTML('beforeend', 
                        `<div class="col-12 mt-3">
                            <div class="alert alert-success">
                                Tìm thấy ${data.data.length} bệnh phù hợp với từ khóa "${searchValue}"
                                <button class="btn btn-sm btn-outline-primary float-end" onclick="location.reload()">
                                    <i class="fas fa-sync-alt me-1"></i>Xem tất cả
                                </button>
                            </div>
                        </div>`
                    );
                } else {
                    diseasesContainer.innerHTML = `
                        <div class="col-12">
                            <div class="alert" style="background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;">
                                Không tìm thấy bệnh phù hợp với từ khóa "${searchValue}"
                                <button class="btn btn-sm btn-outline-primary float-end" onclick="location.reload()">
                                    <i class="fas fa-sync-alt me-1"></i>Xem tất cả
                                </button>
                            </div>
                        </div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                diseasesContainer.innerHTML = `
                    <div class="col-12">
                        <div class="alert" style="background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;">
                            Đã xảy ra lỗi khi tìm kiếm bệnh. Vui lòng thử lại sau.
                            <button class="btn btn-sm btn-outline-primary float-end" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-1"></i>Xem tất cả
                            </button>
                        </div>
                    </div>`;
            });
    }
    
    // Hàm tạo card cho mỗi bệnh
    function createDiseaseCard(disease) {
        const col = document.createElement('div');
        col.className = 'col-md-4 mb-4 disease-card';
        
        col.innerHTML = `
            <div class="card h-100">
                <div class="card-header bg-rose text-white" style="background-color: #f8b4b9 !important;">
                    <h5 class="card-title mb-0">${disease.ten_benh || 'Không có tên'}</h5>
                </div>
                <div class="card-body">
                    <p class="card-text"><strong>Định nghĩa:</strong> ${(disease.dinh_nghia || 'N/A').substring(0, 100)}${disease.dinh_nghia && disease.dinh_nghia.length > 100 ? '...' : ''}</p>
                    <p class="card-text"><strong>Triệu chứng:</strong> ${(disease.trieu_chung || 'N/A').substring(0, 100)}${disease.trieu_chung && disease.trieu_chung.length > 100 ? '...' : ''}</p>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="/diseases/${disease.id}" class="btn btn-primary">
                        <i class="fas fa-eye me-1"></i>Chi tiết
                    </a>
                    
                    @if(Session::has('user'))
                        @if(Session::get('user')['role'] == 'admin')
                        <div>
                            <button type="button" class="btn btn-warning edit-disease" data-id="${disease.id}">
                                <i class="fas fa-edit me-1"></i>Sửa
                            </button>
                            <button type="button" class="btn ms-1 delete-disease" data-id="${disease.id}" 
                                    data-name="${disease.ten_benh || 'Bệnh này'}" style="background-color: #f8b4b9; color: white;">
                                <i class="fas fa-trash me-1"></i>Xóa
                            </button>
                        </div>
                        @else
                        <button type="button" class="btn btn-outline-primary save-favorite-disease" data-id="${disease.id}">
                            <i class="fas fa-bookmark me-1"></i>Lưu bệnh
                        </button>
                        @endif
                    @endif
                </div>
            </div>
        `;
        
        return col;
    }
    
    // Hàm làm nổi bật từ khóa tìm kiếm trong kết quả
    function highlightMatch(text, query) {
        if (!text) return 'Không có tên';
        const regex = new RegExp(query, 'gi');
        return text.replace(regex, match => `<span class="bg-warning">${match}</span>`);
    }
    
    // Hàm rút gọn văn bản dài
    function truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    // Xử lý sự kiện lưu bệnh vào danh sách yêu thích
    const saveFavoriteBtns = document.querySelectorAll('.save-favorite-disease');
    
    saveFavoriteBtns.forEach(button => {
        button.addEventListener('click', function() {
            const diseaseId = this.getAttribute('data-id');
            currentFavoriteBtn = this;
            
            // Hiển thị modal để nhập ghi chú
            document.getElementById('favorite_disease_id').value = diseaseId;
            document.getElementById('disease_note').value = '';
            favoriteModal.show();
        });
    });
    
    // Xử lý sự kiện lưu bệnh yêu thích từ modal
    saveFavoriteDiseaseBtn.addEventListener('click', function() {
        const diseaseId = document.getElementById('favorite_disease_id').value;
        const note = document.getElementById('disease_note').value.trim();
        
        // Đóng modal
        favoriteModal.hide();
        
        if (!currentFavoriteBtn) return;
        
        // Hiển thị trạng thái đang xử lý
        const originalContent = currentFavoriteBtn.innerHTML;
        currentFavoriteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang lưu...';
        currentFavoriteBtn.disabled = true;
        
        // Gửi yêu cầu thêm vào danh sách yêu thích
        fetch('{{ route("add-favorite-disease") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                disease_id: diseaseId,
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
                showToast('Đã lưu bệnh vào danh sách yêu thích!', 'success');
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
            showToast('Đã xảy ra lỗi khi lưu bệnh! Vui lòng thử lại sau.', 'error');
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
    // Các hàm xử lý thêm, sửa, xóa bệnh cho admin
    
    // Thêm bệnh mới
    document.getElementById('saveDiseaseBtn').addEventListener('click', function() {
        const formData = {
            ten_benh: document.getElementById('ten_benh').value,
            dinh_nghia: document.getElementById('dinh_nghia').value,
            nguyen_nhan: document.getElementById('nguyen_nhan').value,
            trieu_chung: document.getElementById('trieu_chung').value,
            chan_doan: document.getElementById('chan_doan').value,
            dieu_tri: document.getElementById('dieu_tri').value
        };

        if (!formData.ten_benh) {
            alert('Vui lòng nhập tên bệnh');
            return;
        }

        // Gửi request API
        fetch('/api/diseases/add', {
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
                var modal = bootstrap.Modal.getInstance(document.getElementById('addDiseaseModal'));
                modal.hide();
                
                // Thông báo thành công và làm mới trang
                alert('Thêm bệnh thành công!');
                location.reload();
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể thêm bệnh'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi thêm bệnh!');
        });
    });

    // Xử lý sự kiện nhấn nút sửa bệnh
    document.querySelectorAll('.edit-disease').forEach(button => {
        button.addEventListener('click', function() {
            const diseaseId = this.getAttribute('data-id');
            
            // Hiển thị trạng thái loading
            const card = this.closest('.card');
            card.style.opacity = '0.7';
            
            // Lấy dữ liệu bệnh
            fetch('/api/diseases/' + diseaseId, {
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
                    const disease = data.data;
                    
                    // Điền dữ liệu vào form
                    document.getElementById('edit_disease_id').value = disease.id;
                    document.getElementById('edit_ten_benh').value = disease.ten_benh || '';
                    document.getElementById('edit_dinh_nghia').value = disease.dinh_nghia || '';
                    document.getElementById('edit_nguyen_nhan').value = disease.nguyen_nhan || '';
                    document.getElementById('edit_trieu_chung').value = disease.trieu_chung || '';
                    document.getElementById('edit_chan_doan').value = disease.chan_doan || '';
                    document.getElementById('edit_dieu_tri').value = disease.dieu_tri || '';
                    
                    // Hiển thị modal
                    var modal = new bootstrap.Modal(document.getElementById('editDiseaseModal'));
                    modal.show();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể lấy thông tin bệnh'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                card.style.opacity = '1';
                alert('Đã xảy ra lỗi khi lấy thông tin bệnh!');
            });
        });
    });

    // Cập nhật thông tin bệnh
    document.getElementById('updateDiseaseBtn').addEventListener('click', function() {
        const diseaseId = document.getElementById('edit_disease_id').value;
        const formData = {
            id: diseaseId,
            ten_benh: document.getElementById('edit_ten_benh').value,
            dinh_nghia: document.getElementById('edit_dinh_nghia').value,
            nguyen_nhan: document.getElementById('edit_nguyen_nhan').value,
            trieu_chung: document.getElementById('edit_trieu_chung').value,
            chan_doan: document.getElementById('edit_chan_doan').value,
            dieu_tri: document.getElementById('edit_dieu_tri').value
        };

        if (!formData.ten_benh) {
            alert('Vui lòng nhập tên bệnh');
            return;
        }

        // Gửi request API
        fetch('/api/diseases/update', {
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
                var modal = bootstrap.Modal.getInstance(document.getElementById('editDiseaseModal'));
                modal.hide();
                
                // Thông báo thành công và làm mới trang
                alert('Cập nhật bệnh thành công!');
                location.reload();
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể cập nhật bệnh'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi cập nhật bệnh!');
        });
    });

    // Xử lý sự kiện xóa bệnh
    document.querySelectorAll('.delete-disease').forEach(button => {
        button.addEventListener('click', function() {
            const diseaseId = this.getAttribute('data-id');
            const diseaseName = this.getAttribute('data-name');
            
            // Cập nhật thông tin vào modal xác nhận
            document.getElementById('delete_disease_id').value = diseaseId;
            document.getElementById('delete_disease_name').textContent = diseaseName;
            
            // Hiển thị modal xác nhận
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteDiseaseModal'));
            confirmModal.show();
        });
    });
    
    // Xử lý sự kiện xác nhận xóa bệnh
    document.getElementById('confirmDeleteDiseaseBtn').addEventListener('click', function() {
        const diseaseId = document.getElementById('delete_disease_id').value;
        const button = document.querySelector(`.delete-disease[data-id="${diseaseId}"]`);
        const card = button.closest('.card');
        
        // Hiển thị trạng thái đang xử lý
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang xóa...';
        card.style.opacity = '0.7';
        
        // Gửi request xóa bệnh
        fetch('/api/diseases/delete', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id: diseaseId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Xóa card khỏi giao diện
                const cardCol = card.closest('.col-md-4');
                cardCol.remove();
                
                // Đóng modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteDiseaseModal'));
                modal.hide();
                
                // Hiển thị toast thông báo thành công
                showToast('Đã xóa bệnh thành công!', 'success');
                
                // Kiểm tra nếu không còn bệnh nào
                if (document.querySelectorAll('.disease-card').length === 0) {
                    document.getElementById('diseases-container').innerHTML = `
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Không có dữ liệu bệnh
                            </div>
                        </div>
                    `;
                }
            } else {
                // Khôi phục trạng thái
                card.style.opacity = '1';
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-trash me-1"></i>Xóa bệnh';
                
                // Hiển thị toast thông báo lỗi
                showToast(data.message || 'Không thể xóa bệnh!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Khôi phục trạng thái
            card.style.opacity = '1';
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-trash me-1"></i>Xóa bệnh';
            
            // Hiển thị toast thông báo lỗi
            showToast('Đã xảy ra lỗi khi xóa bệnh! Vui lòng thử lại sau.', 'error');
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
        fetch('/api/diseases/import', {
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
                alert(`Import thành công! Đã thêm ${data.data.length} bệnh.`);
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