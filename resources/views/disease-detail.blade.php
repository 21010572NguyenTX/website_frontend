@extends('layouts.app')

@section('title', isset($disease['ten_benh']) ? $disease['ten_benh'] : 'Chi tiết bệnh')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mb-3">
            <a href="{{ route('diseases') }}" class="btn btn-outline-danger">
                <i class="fa fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
        <div class="col-md-4 mb-3 text-end">
            @if(Session::has('user'))
            <button type="button" class="btn btn-outline-rose favorite-disease-btn me-2" data-id="{{ $disease['id'] ?? '' }}" style="border-color: #f8b4b9; color: #f8b4b9;">
                <i class="fas fa-heart favorite-icon"></i> <span class="favorite-text">Thêm vào yêu thích</span>
            </button>
            @endif
            @if(Session::has('user') && Session::get('user')['role'] == 'admin')
            <button type="button" class="btn btn-warning edit-disease" data-id="{{ $disease['id'] ?? '' }}">
                <i class="fas fa-edit me-1"></i>Sửa bệnh
            </button>
            <button type="button" class="btn ms-2 delete-disease" data-id="{{ $disease['id'] ?? '' }}" 
                    data-name="{{ $disease['ten_benh'] ?? 'Bệnh này' }}" style="background-color: #f8b4b9; color: white;">
                <i class="fas fa-trash me-1"></i>Xóa bệnh
            </button>
            @endif
        </div>
    </div>

<!-- Chatbot hỗ trợ bệnh -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fas fa-robot me-2"></i>Hỏi đáp về bệnh {{ $disease['ten_benh'] ?? 'này' }}</h5>
                <button id="expand-chat" class="btn btn-sm btn-light position-absolute" style="right: 15px; top: 8px;">
                    <i class="fas fa-expand-alt"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div class="chat-container p-3" id="chat-container" style="height: 300px; overflow-y: auto; border-bottom: 1px solid #eee;">
                    <div id="chat-messages">
                        <div class="message bot-message mb-3">
                            <div class="message-content bg-success text-white p-3" style="border-radius: 15px; display: inline-block; max-width: 90%;">
                                <div class="markdown-content">Xin chào! Tôi có thể giúp bạn tìm hiểu thêm về bệnh <strong>{{ $disease['ten_benh'] ?? 'này' }}</strong>. Bạn có thể hỏi về triệu chứng, cách phòng ngừa hoặc điều trị.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-3">
                    <div class="input-group">
                        <input type="text" id="chat-input" class="form-control" placeholder="Nhập câu hỏi về bệnh này..." aria-label="Nhập câu hỏi về bệnh này">
                        <button class="btn btn-success" type="button" id="send-button">
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
        const diseaseName = "{{ $disease['ten_benh'] ?? 'bệnh này' }}";
        
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
            { role: "system", content: `Bạn là một trợ lý y tế thông minh, giúp người dùng tìm hiểu về bệnh "${diseaseName}". Cung cấp thông tin chính xác, ngắn gọn và hữu ích về triệu chứng, nguyên nhân, cách phòng ngừa và điều trị của bệnh này. Hãy sử dụng định dạng markdown với **in đậm** cho thông tin quan trọng và *in nghiêng* cho thuật ngữ y tế. Trả lời bằng tiếng Việt và nhấn mạnh rằng người dùng nên tham khảo ý kiến bác sĩ. Hãy giữ câu trả lời ngắn gọn nhưng đầy đủ thông tin. Nếu không biết câu trả lời về bệnh cụ thể, hãy nói không đủ thông tin và chỉ cung cấp hướng dẫn chung.` },
            { role: "assistant", content: `Xin chào! Tôi có thể giúp bạn tìm hiểu thêm về bệnh **${diseaseName}**. Bạn có thể hỏi về triệu chứng, cách phòng ngừa hoặc điều trị.` }
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
            messageContent.className = 'message-content bg-success text-white p-3';
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
            messageContent.className = `message-content p-3 ${isUser ? 'bg-primary' : 'bg-success'} text-white`;
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

    @if(isset($disease))
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-rose text-white" style="background-color: #f8b4b9 !important;">
                    <h4 class="mb-0">{{ $disease['ten_benh'] ?? 'Không có tên' }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Định nghĩa</h5>
                                </div>
                                <div class="card-body">
                                    <p>{{ $disease['dinh_nghia'] ?? 'Không có thông tin' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Nguyên nhân</h5>
                                </div>
                                <div class="card-body">
                                    <p>{{ $disease['nguyen_nhan'] ?? 'Không có thông tin' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Triệu chứng</h5>
                                </div>
                                <div class="card-body">
                                    <p>{{ $disease['trieu_chung'] ?? 'Không có thông tin' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Chẩn đoán</h5>
                                </div>
                                <div class="card-body">
                                    <p>{{ $disease['chan_doan'] ?? 'Không có thông tin' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Điều trị</h5>
                                </div>
                                <div class="card-body">
                                    <p>{{ $disease['dieu_tri'] ?? 'Không có thông tin' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <p class="text-muted mb-0">ID: {{ $disease['id'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="alert" style="background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;">
                Không tìm thấy thông tin bệnh.
            </div>
        </div>
    </div>
    @endif
</div>

@if(Session::has('user') && Session::get('user')['role'] == 'admin')
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
                    <input type="hidden" id="edit_disease_id" name="edit_disease_id" value="{{ $disease['id'] ?? '' }}">
                    <div class="mb-3">
                        <label for="edit_ten_benh" class="form-label">Tên bệnh <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_ten_benh" name="edit_ten_benh" value="{{ $disease['ten_benh'] ?? '' }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_dinh_nghia" class="form-label">Định nghĩa</label>
                        <textarea class="form-control" id="edit_dinh_nghia" name="edit_dinh_nghia" rows="3">{{ $disease['dinh_nghia'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_nguyen_nhan" class="form-label">Nguyên nhân</label>
                        <textarea class="form-control" id="edit_nguyen_nhan" name="edit_nguyen_nhan" rows="3">{{ $disease['nguyen_nhan'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_trieu_chung" class="form-label">Triệu chứng</label>
                        <textarea class="form-control" id="edit_trieu_chung" name="edit_trieu_chung" rows="3">{{ $disease['trieu_chung'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_chan_doan" class="form-label">Chẩn đoán</label>
                        <textarea class="form-control" id="edit_chan_doan" name="edit_chan_doan" rows="3">{{ $disease['chan_doan'] ?? '' }}</textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_dieu_tri" class="form-label">Điều trị</label>
                        <textarea class="form-control" id="edit_dieu_tri" name="edit_dieu_tri" rows="3">{{ $disease['dieu_tri'] ?? '' }}</textarea>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý sự kiện nhấn nút sửa bệnh
    document.querySelector('.edit-disease').addEventListener('click', function() {
        // Hiển thị modal
        var modal = new bootstrap.Modal(document.getElementById('editDiseaseModal'));
        modal.show();
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
    document.querySelector('.delete-disease').addEventListener('click', function() {
        const diseaseId = this.getAttribute('data-id');
        const diseaseName = this.getAttribute('data-name');
        
        if (confirm(`Bạn có chắc chắn muốn xóa bệnh "${diseaseName}" không? Hành động này không thể hoàn tác.`)) {
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
                    // Thông báo thành công và chuyển về trang danh sách
                    alert('Xóa bệnh thành công!');
                    window.location.href = '{{ route("diseases") }}';
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể xóa bệnh'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi xóa bệnh!');
            });
        }
    });
});
</script>
@endif

<!-- Script xử lý yêu thích bệnh -->
@if(Session::has('user'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtn = document.querySelector('.favorite-disease-btn');
    const favoriteIcon = document.querySelector('.favorite-icon');
    const favoriteText = document.querySelector('.favorite-text');
    const favoriteModal = new bootstrap.Modal(document.getElementById('addFavoriteDiseaseModal'), {backdrop: 'static'});
    const saveFavoriteDiseaseBtn = document.getElementById('saveFavoriteDiseaseBtn');
    
    if (favoriteBtn) {
        const diseaseId = favoriteBtn.getAttribute('data-id');
        
        // Kiểm tra xem bệnh này đã được yêu thích chưa
        checkFavoriteStatus();
        
        // Xử lý sự kiện click vào nút yêu thích
        favoriteBtn.addEventListener('click', function() {
            if (favoriteBtn.classList.contains('active')) {
                // Nếu đã yêu thích, thì xóa khỏi yêu thích
                removeFavorite();
            } else {
                // Nếu chưa yêu thích, hiển thị modal để nhập ghi chú
                document.getElementById('favorite_disease_id').value = diseaseId;
                document.getElementById('disease_note').value = '';
                favoriteModal.show();
            }
        });
        
        // Hàm kiểm tra trạng thái yêu thích
        function checkFavoriteStatus() {
            fetch(`/api/favoriteDisease/check/${diseaseId}`, {
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
            
            fetch('/api/favoriteDisease/remove', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    disease_id: diseaseId
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
            favoriteBtn.classList.remove('btn-outline-rose');
            favoriteBtn.classList.add('btn-rose');
            favoriteBtn.style = 'background-color: #f8b4b9; color: white;';
            favoriteIcon.classList.add('text-white');
            favoriteIcon.classList.remove('fa-spinner', 'fa-spin');
            favoriteIcon.classList.add('fa-heart');
            favoriteText.textContent = 'Đã yêu thích';
            favoriteBtn.disabled = false;
        }
        
        // Hàm hiển thị trạng thái chưa yêu thích
        function setInactiveState() {
            favoriteBtn.classList.remove('active');
            favoriteBtn.classList.add('btn-outline-rose');
            favoriteBtn.classList.remove('btn-rose');
            favoriteBtn.style = 'border-color: #f8b4b9; color: #f8b4b9;';
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
@endsection