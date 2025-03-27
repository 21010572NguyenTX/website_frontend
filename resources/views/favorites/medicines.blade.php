@extends('layouts.app')

@section('title', 'Thuốc đã lưu')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0"><i class="fas fa-bookmark text-primary me-2"></i>Thuốc đã lưu</h1>
        <a href="{{ route('medicines') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i>Quay lại danh sách
        </a>
    </div>

    @if(isset($error))
        <div class="alert alert-danger">
            {{ $error }}
        </div>
    @endif

    @if(count($favorites) > 0)
        <!-- Kiểm tra dữ liệu -->
        <div class="d-none">
            @foreach($favorites as $key => $fav)
                <pre>{{ json_encode($fav, JSON_PRETTY_PRINT) }}</pre>
            @endforeach
        </div>
        
        <div class="row">
            @foreach($favorites as $favorite)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $favorite['Medicine']['ten_thuoc'] ?? 'Không có tên' }}</h5>
                            <div>
                                <button class="btn btn-sm btn-light me-1 edit-note-btn" 
                                    data-id="{{ $favorite['id'] }}" 
                                    data-note="{{ $favorite['note'] ?? '' }}"
                                    data-medicine-id="{{ $favorite['medicine_id'] ?? $favorite['Medicine']['id'] ?? '' }}"
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
                                <strong>Ghi chú:</strong>
                                <p class="note-content">{{ $favorite['note'] ?? 'Không có ghi chú' }}</p>
                            </div>
                            <a href="{{ route('medicines.show', $favorite['Medicine']['id']) }}" class="btn btn-outline-primary btn-sm">
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
            <i class="fas fa-info-circle me-2"></i>Bạn chưa lưu thuốc nào.
            <a href="{{ route('medicines') }}" class="alert-link">Xem danh sách thuốc</a>
        </div>
    @endif
</div>

<!-- Modal Sửa ghi chú -->
<div class="modal fade" id="editNoteModal" tabindex="-1" aria-labelledby="editNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editNoteModalLabel">Sửa thông tin ghi chú</h5>
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
    // Theo dõi sự kiện mở modal
    const editNoteModal = document.getElementById('editNoteModal');
    if (editNoteModal) {
        editNoteModal.addEventListener('show.bs.modal', function (event) {
            // Lấy button đã kích hoạt modal
            const button = event.relatedTarget;
            
            // Lấy dữ liệu từ button attributes
            const favoriteId = button.getAttribute('data-id');
            const note = button.getAttribute('data-note');
            const medicineId = button.getAttribute('data-medicine-id');
            
            console.log('Modal được mở với dữ liệu:', { favoriteId, note, medicineId });
            
            // Lấy thông tin từ giao diện nếu thuộc tính trống
            let actualNote = note;
            
            if (!actualNote) {
                // Tìm nội dung ghi chú trong card tương ứng
                const card = button.closest('.card');
                const noteContent = card.querySelector('.note-content');
                if (noteContent) {
                    const noteText = noteContent.textContent.trim();
                    if (noteText !== 'Không có ghi chú') {
                        actualNote = noteText;
                    }
                }
            }
            
            console.log('Dữ liệu sau khi cập nhật từ giao diện:', { 
                favoriteId, 
                note: actualNote
            });
            
            // Thiết lập các giá trị vào form
            document.getElementById('favorite_id').value = favoriteId;
            document.getElementById('note').value = actualNote || '';
        });
    }
    
    // Xử lý sự kiện lưu ghi chú
    document.getElementById('saveNoteBtn').addEventListener('click', function() {
        const favoriteId = document.getElementById('favorite_id').value;
        const note = document.getElementById('note').value;
        
        const requestData = {
            user_id: {{ Session::get('user')['id'] ?? 0 }},
            favorite_id: favoriteId,
            note: note
        };
        
        console.log('Gửi yêu cầu cập nhật với dữ liệu:', requestData);
        
        fetch('{{ route("update-favorite-medicine-note") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            console.log('Phản hồi ban đầu từ server:', response);
            return response.json();
        })
        .then(data => {
            console.log('Dữ liệu phản hồi từ server:', data);
            if (data.success) {
                // Kiểm tra dữ liệu trả về
                console.log('Dữ liệu thuốc sau khi cập nhật:', data.data);
                
                // Cập nhật nội dung ghi chú trên giao diện
                const button = document.querySelector(`[data-id="${favoriteId}"]`);
                const card = button.closest('.card');
                card.querySelector('.note-content').textContent = note || 'Không có ghi chú';
                
                // Cập nhật thuộc tính data-note trên nút edit
                button.setAttribute('data-note', note || '');
                console.log('Đã cập nhật thuộc tính nút:', {
                    'data-note': button.getAttribute('data-note')
                });
                
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
            if (confirm('Bạn có chắc chắn muốn xóa thuốc này khỏi danh sách đã lưu?')) {
                // Lấy thông tin
                const favoriteId = this.getAttribute('data-id');
                const button = this;
                const card = button.closest('.col-md-6');
                
                // Hiển thị đang xử lý
                card.style.opacity = '0.5';
                
                // Chuẩn bị dữ liệu
                const data = {
                    favorite_id: parseInt(favoriteId),
                    user_id: {{ Session::get('user')['id'] ?? 0 }}
                };
                
                console.log('Đang gửi yêu cầu xóa thuốc với dữ liệu:', data);
                
                // Sử dụng fetch với phương thức DELETE
                fetch('{{ route("remove-favorite-medicine") }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    console.log('Phản hồi từ server (status):', response.status);
                    return response.text().then(text => {
                        try {
                            // Thử phân tích JSON
                            const json = JSON.parse(text);
                            console.log('Phản hồi từ server (json):', json);
                            return json;
                        } catch (e) {
                            // Nếu không phải JSON, trả về text
                            console.log('Phản hồi từ server (text):', text);
                            throw new Error('Không thể phân tích phản hồi từ server');
                        }
                    });
                })
                .then(data => {
                    console.log('Phản hồi chi tiết:', data);
                    
                    if (data.success === true) {
                        // Xóa phần tử khỏi giao diện
                        card.remove();
                        
                        // Kiểm tra nếu không còn phần tử nào
                        if (document.querySelectorAll('.remove-favorite-btn').length === 0) {
                            document.querySelector('.row').innerHTML = `
                                <div class="alert alert-info w-100">
                                    <i class="fas fa-info-circle me-2"></i>Bạn chưa lưu thuốc nào.
                                    <a href="{{ route('medicines') }}" class="alert-link">Xem danh sách thuốc</a>
                                </div>
                            `;
                        }
                        
                        // Thông báo thành công
                        alert('Đã xóa thuốc khỏi danh sách đã lưu!');
                    } else {
                        // Hiển thị lỗi
                        alert('Lỗi: ' + (data.message || 'Không thể xóa thuốc'));
                        card.style.opacity = '1';
                    }
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    alert('Đã xảy ra lỗi khi xóa thuốc: ' + error.message);
                    card.style.opacity = '1';
                });
            }
        });
    });
});
</script>
@endpush 