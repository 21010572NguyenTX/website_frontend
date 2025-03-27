@extends('layouts.app')

@section('title', isset($medicine['ten_thuoc']) ? $medicine['ten_thuoc'] : 'Chi tiết thuốc')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mb-3">
            <a href="{{ route('medicines') }}" class="btn btn-outline-primary">
                <i class="fa fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
        <div class="col-md-4 mb-3 text-end">
            @if(Session::has('user'))
            <button type="button" class="btn btn-outline-primary favorite-medicine-btn me-2" data-id="{{ $medicine['id'] ?? '' }}">
                <i class="fas fa-heart favorite-icon"></i> <span class="favorite-text">Thêm vào yêu thích</span>
            </button>
            @endif
            @if(Session::has('user') && Session::get('user')['role'] == 'admin')
            <button type="button" class="btn btn-warning edit-medicine" data-id="{{ $medicine['id'] ?? '' }}">
                <i class="fas fa-edit me-1"></i>Sửa thuốc
            </button>
            <button type="button" class="btn btn-danger ms-2 delete-medicine" data-id="{{ $medicine['id'] ?? '' }}" 
                    data-name="{{ $medicine['ten_thuoc'] ?? 'Thuốc này' }}">
                <i class="fas fa-trash me-1"></i>Xóa thuốc
            </button>
            @endif
        </div>
    </div>

<!-- Chatbot hỗ trợ thuốc -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-robot me-2"></i>Hỏi đáp về thuốc {{ $medicine['ten_thuoc'] ?? 'này' }}</h5>
                <button id="expand-chat" class="btn btn-sm btn-light position-absolute" style="right: 15px; top: 8px;">
                    <i class="fas fa-expand-alt"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div class="chat-container p-3" id="chat-container" style="height: 300px; overflow-y: auto; border-bottom: 1px solid #eee;">
                    <div id="chat-messages">
                        <div class="message bot-message mb-3">
                            <div class="message-content bg-primary text-white p-3" style="border-radius: 15px; display: inline-block; max-width: 90%;">
                                <div class="markdown-content">Xin chào! Tôi có thể giúp bạn tìm hiểu thêm về thuốc <strong>{{ $medicine['ten_thuoc'] ?? 'này' }}</strong>. Bạn có thể hỏi về công dụng, liều lượng, tác dụng phụ hoặc cách sử dụng.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-3">
                    <div class="input-group">
                        <input type="text" id="chat-input" class="form-control" placeholder="Nhập câu hỏi về thuốc này..." aria-label="Nhập câu hỏi về thuốc này">
                        <button class="btn btn-primary" type="button" id="send-button">
                            <i class="fas fa-paper-plane"></i> Gửi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Thêm thư viện Marked cho Markdown -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.1/dist/purify.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatInput = document.getElementById('chat-input');
        const sendButton = document.getElementById('send-button');
        const chatMessages = document.getElementById('chat-messages');
        const chatContainer = document.getElementById('chat-container');
        const expandChatBtn = document.getElementById('expand-chat');
        const medicineName = "{{ $medicine['ten_thuoc'] ?? 'thuốc này' }}";
        
        // Khởi tạo marked.js với các tùy chọn
        marked.setOptions({
            breaks: true,
            gfm: true,
            headerIds: false
        });
        
        // API key của DeepSeek
        const DEEPSEEK_API_KEY = 'sk-7e52dba908384c859afb202816677c76';
        
        // Lưu trữ lịch sử trò chuyện
        const conversationHistory = [
            { role: "system", content: `Bạn là một dược sĩ thông minh, giúp người dùng tìm hiểu về thuốc "${medicineName}". Cung cấp thông tin chính xác, ngắn gọn và hữu ích về công dụng, cách dùng, liều lượng, tác dụng phụ và lưu ý khi sử dụng thuốc này. Hãy sử dụng định dạng markdown với **in đậm** cho thông tin quan trọng và *in nghiêng* cho thuật ngữ y tế. Trả lời bằng tiếng Việt và nhấn mạnh rằng người dùng nên tham khảo ý kiến bác sĩ hoặc dược sĩ. Hãy giữ câu trả lời ngắn gọn nhưng đầy đủ thông tin. Nếu không biết câu trả lời về thuốc cụ thể, hãy nói không đủ thông tin và chỉ cung cấp hướng dẫn chung.` },
            { role: "assistant", content: `Xin chào! Tôi có thể giúp bạn tìm hiểu thêm về thuốc **${medicineName}**. Bạn có thể hỏi về công dụng, liều lượng, tác dụng phụ hoặc cách sử dụng.` }
        ];
        
        // Chế độ mở rộng cho chat
        let isExpanded = false;
        expandChatBtn.addEventListener('click', function() {
            isExpanded = !isExpanded;
            
            if (isExpanded) {
                // Mở rộng
                chatContainer.style.height = '500px';
                expandChatBtn.innerHTML = '<i class="fas fa-compress-alt"></i>';
            } else {
                // Thu nhỏ
                chatContainer.style.height = '300px';
                expandChatBtn.innerHTML = '<i class="fas fa-expand-alt"></i>';
            }
            chatContainer.scrollTop = chatContainer.scrollHeight;
        });
        
        // Hàm gọi API DeepSeek với streaming
        async function getAIResponse(userMessage) {
            // Thêm tin nhắn người dùng vào lịch sử
            conversationHistory.push({ role: "user", content: userMessage });
            
            // Tạo message placeholder để streaming content
            const messageElement = addPlaceholderMessage();
            
            try {
                const response = await fetch('https://api.deepseek.com/v1/chat/completions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${DEEPSEEK_API_KEY}`
                    },
                    body: JSON.stringify({
                        model: 'deepseek-chat',
                        messages: conversationHistory,
                        temperature: 0.7,
                        max_tokens: 500,
                        stream: true
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`API Error: ${response.status}`);
                }
                
                // Xử lý phản hồi streaming
                const reader = response.body.getReader();
                const decoder = new TextDecoder("utf-8");
                let fullResponse = '';
                
                while (true) {
                    const { value, done } = await reader.read();
                    if (done) break;
                    
                    const chunk = decoder.decode(value);
                    const lines = chunk.split('\n').filter(line => line.trim() !== '');
                    
                    for (const line of lines) {
                        if (line.startsWith('data: ')) {
                            const data = line.substring(6);
                            if (data === '[DONE]') continue;
                            
                            try {
                                const json = JSON.parse(data);
                                const contentDelta = json.choices[0].delta.content;
                                
                                if (contentDelta) {
                                    fullResponse += contentDelta;
                                    // Cập nhật nội dung tin nhắn với markdown đã được render
                                    updateMessageContent(messageElement, fullResponse);
                                }
                            } catch (e) {
                                console.error('Error parsing chunk:', e);
                            }
                        }
                    }
                }
                
                // Thêm phản hồi đầy đủ vào lịch sử trò chuyện
                conversationHistory.push({ role: "assistant", content: fullResponse });
                
                return fullResponse;
            } catch (error) {
                console.error('Error calling DeepSeek API:', error);
                const errorMessage = 'Xin lỗi, đã xảy ra lỗi khi kết nối với trợ lý AI. Vui lòng thử lại sau.';
                updateMessageContent(messageElement, errorMessage);
                return errorMessage;
            }
        }
        
        // Hàm thêm tin nhắn placeholder cho streaming
        function addPlaceholderMessage() {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message bot-message mb-3';
            
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content bg-primary text-white p-3';
            messageContent.style.borderRadius = '15px';
            messageContent.style.display = 'inline-block';
            messageContent.style.maxWidth = '90%';
            
            const markdownDiv = document.createElement('div');
            markdownDiv.className = 'markdown-content';
            markdownDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang suy nghĩ...';
            
            messageContent.appendChild(markdownDiv);
            messageDiv.appendChild(messageContent);
            chatMessages.appendChild(messageDiv);
            
            // Tự động cuộn xuống tin nhắn mới nhất
            chatContainer.scrollTop = chatContainer.scrollHeight;
            
            return markdownDiv;
        }
        
        // Cập nhật nội dung tin nhắn với markdown
        function updateMessageContent(messageElement, content) {
            // Chuyển đổi markdown thành HTML và làm sạch
            const markedContent = marked.parse(content);
            const cleanHtml = DOMPurify.sanitize(markedContent);
            
            // Cập nhật nội dung
            messageElement.innerHTML = cleanHtml;
            
            // Cuộn xuống
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
        
        // Hàm thêm tin nhắn vào khung chat
        function addMessage(text, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'} mb-3 ${isUser ? 'text-end' : ''}`;
            
            const messageContent = document.createElement('div');
            messageContent.className = `message-content p-3 ${isUser ? 'bg-primary' : 'bg-primary'} text-white`;
            messageContent.style.borderRadius = '15px';
            messageContent.style.display = 'inline-block';
            messageContent.style.maxWidth = '90%';
            
            if (isUser) {
                messageContent.textContent = text;
            } else {
                const markdownDiv = document.createElement('div');
                markdownDiv.className = 'markdown-content';
                const markedContent = marked.parse(text);
                markdownDiv.innerHTML = DOMPurify.sanitize(markedContent);
                messageContent.appendChild(markdownDiv);
            }
            
            messageDiv.appendChild(messageContent);
            chatMessages.appendChild(messageDiv);
            
            // Tự động cuộn xuống tin nhắn mới nhất
            chatContainer.scrollTop = chatContainer.scrollHeight;
            
            return messageDiv;
        }
        
        // Xử lý sự kiện khi nhấn nút gửi
        async function handleSendMessage() {
            const userMessage = chatInput.value.trim();
            
            if (userMessage) {
                // Hiển thị tin nhắn của người dùng
                addMessage(userMessage, true);
                
                // Xóa nội dung input
                chatInput.value = '';
                
                // Vô hiệu hóa nút gửi và input trong khi chờ phản hồi
                sendButton.disabled = true;
                chatInput.disabled = true;
                
                // Gọi API để lấy phản hồi với streaming
                await getAIResponse(userMessage);
                
                // Kích hoạt lại nút gửi và input
                sendButton.disabled = false;
                chatInput.disabled = false;
                chatInput.focus();
            }
        }
        
        // Thêm sự kiện cho nút gửi và khi nhấn Enter
        sendButton.addEventListener('click', handleSendMessage);
        
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleSendMessage();
            }
        });
        
        // Focus vào input khi trang được tải
        chatInput.focus();
        
        // Thêm CSS cho markdown
        const style = document.createElement('style');
        style.textContent = `
            .markdown-content h1, .markdown-content h2, .markdown-content h3 {
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
                font-weight: bold;
            }
            .markdown-content h1 { font-size: 1.5rem; }
            .markdown-content h2 { font-size: 1.3rem; }
            .markdown-content h3 { font-size: 1.1rem; }
            .markdown-content ul, .markdown-content ol {
                padding-left: 1.5rem;
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
            }
            .markdown-content blockquote {
                border-left: 3px solid rgba(255,255,255,0.5);
                padding-left: 1rem;
                margin-left: 0.5rem;
                font-style: italic;
            }
            .markdown-content p {
                margin-bottom: 0.5rem;
            }
            .markdown-content p:last-child {
                margin-bottom: 0;
            }
            .markdown-content a {
                color: #e9f7ff;
                text-decoration: underline;
            }
            .message {
                transition: all 0.3s ease;
            }
        `;
        document.head.appendChild(style);
    });
</script>

    @if(isset($medicine))
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                @if(isset($medicine['hinh_anh']) && !empty($medicine['hinh_anh']))
                    <img src="{{ $medicine['hinh_anh'] }}" class="card-img-top img-fluid" alt="{{ $medicine['ten_thuoc'] }}">
                @else
                    <div class="text-center p-5 bg-light">
                        <i class="fa fa-image fa-5x text-muted"></i>
                        <p class="mt-3">Không có hình ảnh</p>
                    </div>
                @endif
                <div class="card-body">
                    <h4 class="card-title text-center">{{ $medicine['ten_thuoc'] ?? 'Không có tên' }}</h4>
                    <p class="card-text text-center">ID: {{ $medicine['id'] ?? 'N/A' }}</p>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span><strong>Đánh giá:</strong></span>
                        <div>
                            <span class="badge bg-success">Tốt: {{ $medicine['danh_gia_tot'] ?? '0' }}</span>
                            <span class="badge bg-warning text-dark">TB: {{ $medicine['danh_gia_trung_binh'] ?? '0' }}</span>
                            <span class="badge bg-danger">Kém: {{ $medicine['danh_gia_kem'] ?? '0' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Thông tin chi tiết</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <tbody>
                                <tr>
                                    <th style="width: 30%">Tên thuốc</th>
                                    <td>{{ $medicine['ten_thuoc'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Thành phần</th>
                                    <td>{{ $medicine['thanh_phan'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Công dụng</th>
                                    <td>{{ $medicine['cong_dung'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Tác dụng phụ</th>
                                    <td>{{ $medicine['tac_dung_phu'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Nhà sản xuất</th>
                                    <td>{{ $medicine['nha_san_xuat'] ?? 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger">
                Không tìm thấy thông tin thuốc.
            </div>
        </div>
    </div>
    @endif
</div>

@if(Session::has('user') && Session::get('user')['role'] == 'admin')
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
                    <input type="hidden" id="edit_medicine_id" name="edit_medicine_id" value="{{ $medicine['id'] ?? '' }}">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_ten_thuoc" class="form-label">Tên thuốc <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_ten_thuoc" name="edit_ten_thuoc" value="{{ $medicine['ten_thuoc'] ?? '' }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_nha_san_xuat" class="form-label">Nhà sản xuất</label>
                            <input type="text" class="form-control" id="edit_nha_san_xuat" name="edit_nha_san_xuat" value="{{ $medicine['nha_san_xuat'] ?? '' }}">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_thanh_phan" class="form-label">Thành phần</label>
                        <textarea class="form-control" id="edit_thanh_phan" name="edit_thanh_phan" rows="2">{{ $medicine['thanh_phan'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_cong_dung" class="form-label">Công dụng</label>
                        <textarea class="form-control" id="edit_cong_dung" name="edit_cong_dung" rows="3">{{ $medicine['cong_dung'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_tac_dung_phu" class="form-label">Tác dụng phụ</label>
                        <textarea class="form-control" id="edit_tac_dung_phu" name="edit_tac_dung_phu" rows="2">{{ $medicine['tac_dung_phu'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_hinh_anh" class="form-label">URL hình ảnh</label>
                        <input type="url" class="form-control" id="edit_hinh_anh" name="edit_hinh_anh" value="{{ $medicine['hinh_anh'] ?? '' }}" placeholder="https://example.com/image.jpg">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_danh_gia_tot" class="form-label">Đánh giá tốt</label>
                            <input type="number" class="form-control" id="edit_danh_gia_tot" name="edit_danh_gia_tot" min="0" value="{{ $medicine['danh_gia_tot'] ?? 0 }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_danh_gia_trung_binh" class="form-label">Đánh giá trung bình</label>
                            <input type="number" class="form-control" id="edit_danh_gia_trung_binh" name="edit_danh_gia_trung_binh" min="0" value="{{ $medicine['danh_gia_trung_binh'] ?? 0 }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_danh_gia_kem" class="form-label">Đánh giá kém</label>
                            <input type="number" class="form-control" id="edit_danh_gia_kem" name="edit_danh_gia_kem" min="0" value="{{ $medicine['danh_gia_kem'] ?? 0 }}">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý sự kiện nhấn nút sửa thuốc
    document.querySelector('.edit-medicine').addEventListener('click', function() {
        // Hiển thị modal
        var modal = new bootstrap.Modal(document.getElementById('editMedicineModal'));
        modal.show();
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
        fetch('{{ route("admin.medicines.update") }}', {
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
    document.querySelector('.delete-medicine').addEventListener('click', function() {
        const medicineId = this.getAttribute('data-id');
        const medicineName = this.getAttribute('data-name');
        
        if (confirm(`Bạn có chắc chắn muốn xóa thuốc "${medicineName}" không? Hành động này không thể hoàn tác.`)) {
            // Gửi request xóa thuốc
            fetch('{{ route("admin.medicines.delete") }}', {
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
                    // Thông báo thành công và chuyển về trang danh sách
                    alert('Xóa thuốc thành công!');
                    window.location.href = '{{ route("medicines") }}';
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể xóa thuốc'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi xóa thuốc!');
            });
        }
    });
});
</script>
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

<!-- Script xử lý yêu thích thuốc -->
@if(Session::has('user'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtn = document.querySelector('.favorite-medicine-btn');
    const favoriteIcon = document.querySelector('.favorite-icon');
    const favoriteText = document.querySelector('.favorite-text');
    const favoriteModal = new bootstrap.Modal(document.getElementById('addFavoriteMedicineModal'), {backdrop: 'static'});
    const saveFavoriteMedicineBtn = document.getElementById('saveFavoriteMedicineBtn');
    
    if (favoriteBtn) {
        const medicineId = favoriteBtn.getAttribute('data-id');
        
        // Kiểm tra xem thuốc này đã được yêu thích chưa
        checkFavoriteStatus();
        
        // Xử lý sự kiện click vào nút yêu thích
        favoriteBtn.addEventListener('click', function() {
            if (favoriteBtn.classList.contains('active')) {
                // Nếu đã yêu thích, thì xóa khỏi yêu thích
                removeFavorite();
            } else {
                // Nếu chưa yêu thích, hiển thị modal để nhập ghi chú
                document.getElementById('favorite_medicine_id').value = medicineId;
                document.getElementById('medicine_note').value = '';
                favoriteModal.show();
            }
        });
        
        // Xử lý sự kiện lưu thuốc yêu thích từ modal
        saveFavoriteMedicineBtn.addEventListener('click', function() {
            const note = document.getElementById('medicine_note').value.trim();
            
            // Đóng modal
            favoriteModal.hide();
            
            // Hiển thị trạng thái đang xử lý
            favoriteBtn.disabled = true;
            const originalContent = favoriteText.textContent;
            favoriteIcon.classList.add('fa-spinner', 'fa-spin');
            favoriteIcon.classList.remove('fa-heart');
            favoriteText.textContent = 'Đang lưu...';
            
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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setActiveState();
                    showToast('Đã thêm vào danh sách yêu thích!', 'success');
                } else {
                    // Khôi phục trạng thái nút
                    favoriteBtn.disabled = false;
                    favoriteIcon.classList.remove('fa-spinner', 'fa-spin');
                    favoriteIcon.classList.add('fa-heart');
                    favoriteText.textContent = originalContent;
                    
                    showToast(data.message || 'Không thể thêm vào yêu thích!', 'error');
                }
            })
            .catch(error => {
                console.error('Error adding favorite:', error);
                
                // Khôi phục trạng thái nút
                favoriteBtn.disabled = false;
                favoriteIcon.classList.remove('fa-spinner', 'fa-spin');
                favoriteIcon.classList.add('fa-heart');
                favoriteText.textContent = originalContent;
                
                showToast('Đã xảy ra lỗi khi thêm vào yêu thích!', 'error');
            });
        });
        
        // Hàm kiểm tra trạng thái yêu thích
        function checkFavoriteStatus() {
            fetch(`/api/favoritesMedicine/check/${medicineId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.isFavorite) {
                    setActiveState();
                } else {
                    setInactiveState();
                }
            })
            .catch(error => {
                console.error('Error checking favorite status:', error);
            });
        }
        
        // Hàm xóa khỏi yêu thích
        function removeFavorite() {
            // Hiển thị trạng thái đang xử lý
            favoriteBtn.disabled = true;
            const originalContent = favoriteText.textContent;
            favoriteIcon.classList.add('fa-spinner', 'fa-spin');
            favoriteIcon.classList.remove('fa-heart');
            favoriteText.textContent = 'Đang xóa...';
            
            fetch('/api/favoritesMedicine/remove', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    medicine_id: medicineId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setInactiveState();
                    showToast('Đã xóa khỏi danh sách yêu thích!', 'info');
                } else {
                    // Khôi phục trạng thái nút
                    favoriteBtn.disabled = false;
                    favoriteIcon.classList.remove('fa-spinner', 'fa-spin');
                    favoriteIcon.classList.add('fa-heart');
                    favoriteText.textContent = originalContent;
                    
                    showToast(data.message || 'Không thể xóa khỏi yêu thích!', 'error');
                }
            })
            .catch(error => {
                console.error('Error removing favorite:', error);
                
                // Khôi phục trạng thái nút
                favoriteBtn.disabled = false;
                favoriteIcon.classList.remove('fa-spinner', 'fa-spin');
                favoriteIcon.classList.add('fa-heart');
                favoriteText.textContent = originalContent;
                
                showToast('Đã xảy ra lỗi khi xóa khỏi yêu thích!', 'error');
            });
        }
        
        // Hàm hiển thị trạng thái đã yêu thích
        function setActiveState() {
            favoriteBtn.classList.add('active');
            favoriteBtn.classList.remove('btn-outline-primary');
            favoriteBtn.classList.add('btn-primary');
            favoriteIcon.classList.add('text-white');
            favoriteIcon.classList.remove('fa-spinner', 'fa-spin');
            favoriteIcon.classList.add('fa-heart');
            favoriteText.textContent = 'Đã yêu thích';
            favoriteBtn.disabled = false;
        }
        
        // Hàm hiển thị trạng thái chưa yêu thích
        function setInactiveState() {
            favoriteBtn.classList.remove('active');
            favoriteBtn.classList.add('btn-outline-primary');
            favoriteBtn.classList.remove('btn-primary');
            favoriteIcon.classList.remove('text-white');
            favoriteIcon.classList.remove('fa-spinner', 'fa-spin');
            favoriteIcon.classList.add('fa-heart');
            favoriteText.textContent = 'Thêm vào yêu thích';
            favoriteBtn.disabled = false;
        }
        
        // Hàm hiển thị thông báo
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
    }
});
</script>
@endif
@endsection