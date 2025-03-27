@extends('layouts.app')

@section('title', 'Bệnh đã lưu')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="fas fa-bookmark text-primary me-2"></i>Bệnh đã lưu</h1>
        <a href="{{ route('diseases') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i>Quay lại danh sách
        </a>
    </div>

    @if(isset($error))
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @endif

    @if(count($favorites) > 0)
        <div class="row">
            @foreach($favorites as $favorite)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $favorite['Disease']['ten_benh'] ?? 'Không có tên' }}</h5>
                            <div>
                                <button class="btn btn-sm btn-light me-1 edit-note-btn" 
                                    data-id="{{ $favorite['id'] }}" 
                                    data-note="{{ $favorite['note'] ?? '' }}" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editNoteModal">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger remove-favorite-btn" 
                                    data-id="{{ $favorite['id'] }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Mô tả:</strong> 
                                <p>{{ Str::limit($favorite['Disease']['mo_ta'] ?? 'Không có thông tin', 150) }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Triệu chứng:</strong> 
                                <p>{{ Str::limit($favorite['Disease']['trieu_chung'] ?? 'Không có thông tin', 150) }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Ghi chú:</strong>
                                <p class="note-content">{{ $favorite['note'] ?? 'Không có ghi chú' }}</p>
                            </div>
                            <a href="{{ route('diseases.show', $favorite['Disease']['id']) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-info-circle me-1"></i>Xem chi tiết
                            </a>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Đã lưu: {{ isset($favorite['created_at']) ? date('d/m/Y', strtotime($favorite['created_at'])) : 'Không xác định' }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>Bạn chưa lưu bệnh nào.
            <a href="{{ route('diseases') }}" class="alert-link">Xem danh sách bệnh</a>
        </div>
    @endif
</div>

<!-- Modal Sửa ghi chú -->
<div class="modal fade" id="editNoteModal" tabindex="-1" aria-labelledby="editNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editNoteModalLabel">Sửa thông tin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editNoteForm">
                    <input type="hidden" id="favorite_id" name="favorite_id" value="">
                    <div class="mb-3">
                        <label for="note" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="saveNoteBtn">Lưu</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý sự kiện nút sửa ghi chú
    document.querySelectorAll('.edit-note-btn').forEach(button => {
        button.addEventListener('click', function() {
            const favoriteId = this.getAttribute('data-id');
            const note = this.getAttribute('data-note');
            
            document.getElementById('favorite_id').value = favoriteId;
            
            document.getElementById('note').value = note;
        });
    });
    
    // Xử lý sự kiện lưu ghi chú
    document.getElementById('saveNoteBtn').addEventListener('click', function() {
        const favoriteId = document.getElementById('favorite_id').value;
        const note = document.getElementById('note').value;
        
        const requestData = {
            user_id: {{ Session::get('user')['id'] ?? 0 }},
            favorite_id: favoriteId,
            note: note
        };
        
        fetch('{{ route("update-favorite-disease-note") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cập nhật nội dung ghi chú trên giao diện
                const card = document.querySelector(`[data-id="${favoriteId}"]`).closest('.card');
                card.querySelector('.note-content').textContent = note || 'Không có ghi chú';
                
                // Đóng modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editNoteModal'));
                modal.hide();
                
                // Hiển thị thông báo thành công
                alert('Cập nhật thông tin thành công!');
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi cập nhật ghi chú');
        });
    });
    
    // Xử lý sự kiện xóa yêu thích
    document.querySelectorAll('.remove-favorite-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Bạn có chắc chắn muốn xóa bệnh này khỏi danh sách đã lưu?')) {
                // Lấy thông tin
                const favoriteId = this.getAttribute('data-id');
                const button = this;
                const card = button.closest('.col-md-6');
                
                // Hiển thị đang xử lý
                card.style.opacity = '0.5';
                
                // Tạo form data để gửi qua AJAX
                const formData = new FormData();
                formData.append('favorite_id', favoriteId);
                formData.append('user_id', {{ Session::get('user')['id'] ?? 0 }});
                formData.append('_token', '{{ csrf_token() }}');
                
                // Sử dụng XMLHttpRequest thay vì fetch
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route("remove-favorite-disease") }}', true);
                
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            console.log('Phản hồi:', response);
                            
                            if (response.success === true) {
                                // Xóa phần tử khỏi giao diện
                                card.remove();
                                
                                // Kiểm tra nếu không còn phần tử nào
                                if (document.querySelectorAll('.remove-favorite-btn').length === 0) {
                                    document.querySelector('.row').innerHTML = `
                                        <div class="alert alert-info w-100">
                                            <i class="fas fa-info-circle me-2"></i>Bạn chưa lưu bệnh nào.
                                            <a href="{{ route('diseases') }}" class="alert-link">Xem danh sách bệnh</a>
                                        </div>
                                    `;
                                }
                                
                                // Thông báo thành công
                                alert('Đã xóa bệnh khỏi danh sách đã lưu!');
                            } else {
                                // Hiển thị lỗi
                                alert('Lỗi: ' + (response.message || 'Không thể xóa bệnh'));
                                card.style.opacity = '1';
                            }
                        } catch (e) {
                            console.error('Lỗi phân tích JSON:', e);
                            alert('Lỗi xử lý dữ liệu khi xóa bệnh');
                            card.style.opacity = '1';
                        }
                    } else {
                        console.error('Lỗi HTTP:', xhr.status);
                        alert('Lỗi kết nối khi xóa bệnh');
                        card.style.opacity = '1';
                    }
                };
                
                xhr.onerror = function() {
                    console.error('Lỗi kết nối');
                    alert('Không thể kết nối đến máy chủ');
                    card.style.opacity = '1';
                };
                
                // Gửi request
                xhr.send(formData);
                console.log('Đã gửi yêu cầu xóa bệnh với ID:', favoriteId);
            }
        });
    });
});
</script>
@endpush 