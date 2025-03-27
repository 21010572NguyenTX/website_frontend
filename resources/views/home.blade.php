@extends('layouts.app')

@section('title', 'Trang chủ - Hệ thống quản lý thuốc và bệnh')

@section('content')
<div class="row justify-content-center text-center my-5">
    <div class="col-md-8">
        <h1 class="display-4 mb-4">Hệ thống quản lý thuốc và bệnh</h1>
        <p class="lead">Tra cứu thông tin về thuốc và bệnh một cách dễ dàng</p>
    </div>
</div>

<!-- Phần chatbot hỗ trợ sức khỏe -->
<div class="row mt-5">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-success text-white text-center">
                <h3 class="card-title"><i class="fas fa-robot me-2"></i>Trợ lý sức khỏe thông minh</h3>
                <button id="expand-chat" class="btn btn-sm btn-light position-absolute" style="right: 15px; top: 12px;">
                    <i class="fas fa-expand-alt"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-md-3 bg-light p-4 border-end" id="chat-sidebar">
                        <div class="text-center">
                            <i class="fas fa-comment-medical fa-5x text-success mb-4"></i>
                            <h4>Hỏi đáp về sức khỏe với AI</h4>
                            <p>Đặt câu hỏi về các vấn đề sức khỏe, triệu chứng bệnh hoặc thông tin về thuốc - được hỗ trợ bởi mô hình ngôn ngữ lớn AI </p>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Mẹo:</strong> Hãy mô tả chi tiết triệu chứng hoặc đặt câu hỏi cụ thể để nhận được câu trả lời tốt nhất
                            </div>
                            <div class="d-grid gap-2 mt-4">
                                <button class="btn btn-outline-success btn-sm quick-question" data-question="Triệu chứng cảm cúm là gì?">
                                    <i class="fas fa-virus me-1"></i> Triệu chứng cảm cúm
                                </button>
                                <button class="btn btn-outline-success btn-sm quick-question" data-question="Cách điều trị đau đầu tại nhà?">
                                    <i class="fas fa-head-side-virus me-1"></i> Điều trị đau đầu
                                </button>
                                <button class="btn btn-outline-success btn-sm quick-question" data-question="Thông tin về thuốc Paracetamol?">
                                    <i class="fas fa-pills me-1"></i> Về thuốc Paracetamol
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9" id="chat-main">
                        <div class="chat-container p-3" id="chat-container" style="height: 400px; overflow-y: auto; border-bottom: 1px solid #eee;">
                            <div id="chat-messages">
                                <div class="message bot-message mb-3">
                                    <div class="message-content bg-success text-white p-3" style="border-radius: 15px; display: inline-block; max-width: 90%;">
                                        <div class="markdown-content">Xin chào! Tôi là trợ lý sức khỏe thông minh được hỗ trợ bởi DeepSeek AI. Tôi có thể giúp trả lời các câu hỏi về sức khỏe, thuốc men hoặc các vấn đề y tế. Bạn có thể hỏi tôi bất cứ điều gì!</div>
                                    </div>
                                </div>
                                <!-- Các tin nhắn sẽ được thêm vào đây bằng JavaScript -->
                            </div>
                        </div>
                        <div class="p-3">
                            <div class="input-group">
                                <input type="text" id="chat-input" class="form-control" placeholder="Nhập câu hỏi về sức khỏe..." aria-label="Nhập câu hỏi về sức khỏe">
                                <button class="btn btn-success" type="button" id="send-button">
                                    <i class="fas fa-paper-plane"></i> Gửi
                                </button>
                            </div>
                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    <i class="fas fa-info-circle"></i> Ví dụ: "Tôi bị đau đầu và sốt", "Triệu chứng của cảm cúm là gì?"
                                </div>
                                <div>
                                    <small class="text-muted">Powered by <span class="fw-bold">PhenikaaMedHelper</span></small>
                                </div>
                            </div>
                        </div>
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
        
        // Khởi tạo marked.js với các tùy chọn
        marked.setOptions({
            breaks: true,         // Cho phép xuống dòng với một dòng mới
            gfm: true,            // Sử dụng GitHub Flavored Markdown
            headerIds: false      // Không thêm id vào các tiêu đề
        });
        
        // API key của DeepSeek
        const DEEPSEEK_API_KEY = 'sk-7e52dba908384c859afb202816677c76';
        
        // Lưu trữ lịch sử trò chuyện
        const conversationHistory = [
            { role: "system", content: "Bạn là một trợ lý y tế thông minh, giúp người dùng tìm hiểu về các vấn đề sức khỏe, thuốc men và triệu chứng bệnh. Hãy cung cấp thông tin chính xác, dễ hiểu và mang tính xây dựng. Luôn nhắc nhở người dùng tham khảo ý kiến bác sĩ cho các vấn đề y tế nghiêm trọng. Sử dụng định dạng markdown để trình bày câu trả lời rõ ràng và có cấu trúc: dùng **in đậm** cho thông tin quan trọng, *in nghiêng* cho thuật ngữ y tế, và danh sách cho các bước hoặc triệu chứng. Sử dụng ## cho các tiêu đề, và dùng > để nhấn mạnh cảnh báo. Giữ câu trả lời ngắn gọn nhưng đầy đủ thông tin, và luôn trả lời bằng tiếng Việt." },
            { role: "assistant", content: "Xin chào! Tôi là trợ lý sức khỏe thông minh được hỗ trợ bởi DeepSeek AI. Tôi có thể giúp trả lời các câu hỏi về sức khỏe, thuốc men hoặc các vấn đề y tế. Bạn có thể hỏi tôi bất cứ điều gì!" }
        ];
        
        // Chế độ mở rộng cho chat
        let isExpanded = false;
        expandChatBtn.addEventListener('click', function() {
            isExpanded = !isExpanded;
            const chatMain = document.getElementById('chat-main');
            const chatSidebar = document.getElementById('chat-sidebar');
            const chatContainer = document.getElementById('chat-container');
            
            if (isExpanded) {
                // Mở rộng
                chatContainer.style.height = '600px';
                chatSidebar.classList.add('d-none');
                chatMain.classList.remove('col-md-9');
                chatMain.classList.add('col-md-12');
                expandChatBtn.innerHTML = '<i class="fas fa-compress-alt"></i>';
            } else {
                // Thu nhỏ
                chatContainer.style.height = '400px';
                chatSidebar.classList.remove('d-none');
                chatMain.classList.remove('col-md-12');
                chatMain.classList.add('col-md-9');
                expandChatBtn.innerHTML = '<i class="fas fa-expand-alt"></i>';
            }
            chatContainer.scrollTop = chatContainer.scrollHeight;
        });
        
        // Xử lý câu hỏi nhanh
        document.querySelectorAll('.quick-question').forEach(button => {
            button.addEventListener('click', function() {
                const question = this.getAttribute('data-question');
                chatInput.value = question;
                handleSendMessage();
            });
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
                        max_tokens: 800,
                        stream: true // Kích hoạt streaming
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
                // Xử lý lỗi
                console.error('Error calling DeepSeek API:', error);
                
                // Thông báo lỗi
                const errorMessage = 'Xin lỗi, đã xảy ra lỗi khi kết nối với trợ lý AI. Vui lòng thử lại sau hoặc tham khảo mục thuốc và bệnh của chúng tôi.';
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
            
            // Nếu là tin nhắn người dùng, hiển thị nguyên bản
            // Nếu là tin nhắn bot, áp dụng markdown
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
            .markdown-content table {
                border-collapse: collapse;
                margin: 0.5rem 0;
                width: 100%;
            }
            .markdown-content th, .markdown-content td {
                border: 1px solid rgba(255,255,255,0.3);
                padding: 0.25rem 0.5rem;
            }
            .message {
                transition: all 0.3s ease;
            }
        `;
        document.head.appendChild(style);
    });
</script>

<div class="row mt-5">
    <div class="col-md-6">
        <div class="card h-100 shadow">
            <div class="card-header bg-primary text-white text-center">
                <h3 class="card-title"><i class="fas fa-pills me-2"></i>Danh sách thuốc</h3>
            </div>
            <div class="card-body text-center">
                <i class="fas fa-capsules fa-5x text-primary mb-4"></i>
                <p class="card-text">Xem thông tin chi tiết về các loại thuốc, công dụng, thành phần và đánh giá</p>
                <a href="{{ route('medicines') }}" class="btn btn-primary btn-lg mt-3">
                    <i class="fas fa-search me-2"></i>Xem danh sách thuốc
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 shadow">
            <div class="card-header bg-danger text-white text-center">
                <h3 class="card-title"><i class="fas fa-virus me-2"></i>Danh sách bệnh</h3>
            </div>
            <div class="card-body text-center">
                <i class="fas fa-heartbeat fa-5x text-danger mb-4"></i>
                <p class="card-text">Xem thông tin chi tiết về các loại bệnh, triệu chứng, nguyên nhân và phương pháp điều trị</p>
                <a href="{{ route('diseases') }}" class="btn btn-danger btn-lg mt-3">
                    <i class="fas fa-search me-2"></i>Xem danh sách bệnh
                </a>
            </div>
        </div>
    </div>
</div>

@endsection