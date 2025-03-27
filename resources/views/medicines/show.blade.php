                <div class="card-body">
                    <h2 class="card-title mb-3">{{ $medicine['ten_thuoc'] }}</h2>
                    
                    @if(Session::has('user'))
                    <div class="mb-4">
                        <button id="addToFavoriteBtn" class="btn btn-outline-primary me-2">
                            <i class="fas fa-bookmark me-1"></i> Lưu thuốc này
                        </button>
                    </div>
                    @endif
                    
                    <div class="row mb-4"> 

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addToFavoriteBtn = document.getElementById('addToFavoriteBtn');
    
    if (addToFavoriteBtn) {
        addToFavoriteBtn.addEventListener('click', function() {
            @if(!Session::has('user'))
                window.location.href = "{{ route('login') }}";
                return;
            @endif
            
            // Hiển thị modal nhập ghi chú
            const note = prompt('Nhập ghi chú (không bắt buộc):');
            
            // Tiến hành gửi request thêm vào bookmark
            fetch('{{ route("add-favorite-medicine") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    medicine_id: {{ $medicine['id'] }},
                    note: note || ''
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đã lưu thuốc này vào danh sách!');
                    addToFavoriteBtn.innerHTML = '<i class="fas fa-check me-1"></i> Đã lưu';
                    addToFavoriteBtn.classList.remove('btn-outline-primary');
                    addToFavoriteBtn.classList.add('btn-success');
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi lưu thuốc!');
            });
        });
    }
});
</script>
@endpush 