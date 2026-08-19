<style>
    /* --- 1. CSS CHO BONG BÓNG MỜI GỌI (GREETING BUBBLE) --- */
    .chat-greeting {
        position: fixed;
        bottom: 90px;
        right: 35px;
        background: white;
        color: #333;
        padding: 12px 20px;
        border-radius: 20px 20px 5px 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        font-size: 14px;
        font-weight: 500;
        opacity: 0;
        transform: translateY(20px) scale(0.8);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        pointer-events: none;
        z-index: 9998; /* Nằm dưới khung chat nhưng trên các phần tử khác */
        max-width: 250px;
    }

    .chat-greeting.show {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }

    /* Mũi tên tam giác của bong bóng */
    .chat-greeting::after {
        content: '';
        position: absolute;
        bottom: -6px;
        right: 15px;
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-top: 8px solid white;
    }

    /* Nút tắt bong bóng (x) */
    .close-greeting {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #ff4d4f;
        color: white;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        text-align: center;
        line-height: 16px;
        font-size: 12px;
        cursor: pointer;
        display: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .chat-greeting:hover .close-greeting {
        display: block;
    }

    /* --- 2. CSS CHO NÚT TRÒN (TOGGLER) --- */
    .chatbot-toggler {
        position: fixed;
        bottom: 30px;
        right: 35px;
        outline: none;
        border: none;
        height: 50px;
        width: 50px;
        display: flex;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #724ae8;
        transition: all 0.2s ease;
        z-index: 9999;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
    
    /* Hiệu ứng xoay khi mở chat */
    body.show-chatbot .chatbot-toggler { transform: rotate(90deg); }
    .chatbot-toggler i { color: #fff; font-size: 20px; transition: all 0.2s ease;}
    .chatbot-toggler i:last-child,
    body.show-chatbot .chatbot-toggler i:first-child { display: none; }
    body.show-chatbot .chatbot-toggler i:last-child { display: block; }

    /* --- 3. CSS CHO KHUNG CHAT CHÍNH --- */
    .chatbot {
        position: fixed;
        right: 35px;
        bottom: 90px;
        width: 350px;
        background: #fff;
        border-radius: 15px;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
        transform: scale(0.5);
        transform-origin: bottom right;
        box-shadow: 0 0 128px 0 rgba(0,0,0,0.1), 0 32px 64px -48px rgba(0,0,0,0.5);
        transition: all 0.1s ease;
        z-index: 9999;
    }
    body.show-chatbot .chatbot {
        opacity: 1;
        pointer-events: auto;
        transform: scale(1);
    }

    .chatbot header {
        background: #724ae8;
        padding: 16px 0;
        position: relative;
        text-align: center;
        color: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .chatbot header span { font-weight: bold; font-size: 1.1rem; }
    .chatbot header .close-btn {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transition: background 0.2s;
    }
    .chatbot header .close-btn:hover {
        background: rgba(255, 255, 255, 0.4);
    }

    .chatbox {
        overflow-y: auto;
        height: 350px; /* Điều chỉnh độ cao */
        padding: 15px 20px 70px;
        background: #f2f2f2;
    }

    .chatbox .chat { display: flex; list-style: none; margin-bottom: 15px; }
    
    .chatbox .incoming span {
        background: #e2e2e2;
        color: #000;
        border-radius: 10px 10px 10px 0;
        padding: 10px 15px;
        max-width: 80%;
        font-size: 0.95rem;
        line-height: 1.4;
    }
    
    .chatbox .outgoing { justify-content: flex-end; }
    .chatbox .outgoing span {
        background: #724ae8;
        color: #fff;
        border-radius: 10px 10px 0 10px;
        padding: 10px 15px;
        max-width: 80%;
        font-size: 0.95rem;
        line-height: 1.4;
    }

    .chat-input {
        position: absolute;
        bottom: 0;
        width: 100%;
        display: flex;
        gap: 5px;
        background: #fff;
        padding: 5px 20px;
        border-top: 1px solid #ddd;
    }
    .chat-input textarea {
        height: 55px;
        width: 100%;
        border: none;
        outline: none;
        resize: none;
        padding: 16px 0;
        font-size: 0.95rem;
        font-family: inherit;
    }
    .chat-input span {
        align-self: center;
        color: #724ae8;
        cursor: pointer;
        height: 55px;
        display: flex;
        align-items: center;
        font-size: 1.2rem;
        transition: color 0.2s;
    }
    .chat-input span:hover { color: #5a38b5; }

    /* Responsive cho Mobile */
    @media (max-width: 490px) {
        .chatbot {
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            border-radius: 0;
        }
        .chatbot .chatbox { height: 90%; }
        .chatbot header { padding: 20px 0; }
        .chat-greeting { display: none; } /* Ẩn bong bóng trên mobile để đỡ vướng */
    }
</style>

<div id="chat-greeting" class="chat-greeting">
    👋 Chào bạn, mình có thể giúp gì cho bạn?
    <div class="close-greeting" onclick="closeGreeting()">×</div>
</div>

<button class="chatbot-toggler" onclick="toggleChat()">
    <i class="fa-solid fa-message"></i>
    <i class="fa-solid fa-xmark"></i>
</button>

<div class="chatbot">
    <header>
        <span>Trợ lý CampingShop</span>
        <span class="close-btn" onclick="toggleChat()"><i class="fa-solid fa-xmark"></i></span>
    </header>
    
    <ul class="chatbox" id="chatbox">
        <li class="chat incoming">
            <span>Xin chào! Mình là trợ lý ảo CampingShop. Mình có thể giúp gì cho bạn?</span>
        </li>
    </ul>
    
    <div class="chat-input">
        <textarea placeholder="Nhập câu hỏi..." spellcheck="false" id="chatInput" required></textarea>
        <span id="sendBtn" onclick="handleChat()"><i class="fa-solid fa-paper-plane"></i></span>
    </div>
</div>

<script>
    const chatInput = document.getElementById("chatInput");
    const sendBtn = document.getElementById("sendBtn");
    const chatBox = document.getElementById("chatbox");
    const greetingBubble = document.getElementById('chat-greeting');

    // --- LOGIC 1: BONG BÓNG MỜI GỌI (GREETING) ---
    const SHOW_DELAY = 5000;     // 5 giây hiện lần đầu
    const HIDE_DELAY = 8000;     // 8 giây tự ẩn
    const REPEAT_INTERVAL = 60000; // 60 giây hiện lại nếu chưa chat

    function showGreeting() {
        // Chỉ hiện nếu khung chat đang đóng
        if (!document.body.classList.contains("show-chatbot")) {
            greetingBubble.classList.add('show');
            setTimeout(() => {
                greetingBubble.classList.remove('show');
            }, HIDE_DELAY);
        }
    }

    function closeGreeting() {
        greetingBubble.classList.remove('show');
    }

    // Thiết lập hẹn giờ
    setTimeout(showGreeting, SHOW_DELAY);
    setInterval(showGreeting, REPEAT_INTERVAL);

    // --- LOGIC 2: ĐÓNG/MỞ KHUNG CHAT ---
    function toggleChat() {
        document.body.classList.toggle("show-chatbot");
        // Khi mở chat thì ẩn luôn bong bóng
        if(document.body.classList.contains("show-chatbot")){
            closeGreeting();
        }
    }

    // --- LOGIC 3: XỬ LÝ TIN NHẮN ---
    const createChatLi = (message, className) => {
        const chatLi = document.createElement("li");
        chatLi.classList.add("chat", className);
        // Nếu là tin nhắn đến (incoming), có thể thêm icon robot nếu muốn
        let content = className === "outgoing" 
            ? `<span>${message}</span>` 
            : `<span>${message}</span>`; 
            
        chatLi.innerHTML = content;
        return chatLi;
    }

    const handleChat = async () => {
        const userMessage = chatInput.value.trim();
        if (!userMessage) return;

        // 1. Thêm tin nhắn User
        chatBox.appendChild(createChatLi(userMessage, "outgoing"));
        chatInput.value = "";
        chatBox.scrollTo(0, chatBox.scrollHeight);

        // 2. Thêm loading
        const loadingLi = createChatLi("Đang suy nghĩ...", "incoming");
        chatBox.appendChild(loadingLi);
        chatBox.scrollTo(0, chatBox.scrollHeight);

        try {
            // Get CSRF token from meta tag
            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : '';

            // 3. Gọi API (Sửa URL này đúng với Route của bạn)
            const response = await fetch('/chat/sendMessage', { 
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ message: userMessage })
            });

            if (!response.ok) {
                if (response.status === 403) {
                    throw new Error("Bảo mật CSRF failed");
                }
                throw new Error("Network response was not ok");
            }
            
            const data = await response.json();
            
            // 4. Cập nhật câu trả lời từ AI
            // Xử lý xuống dòng \n thành thẻ <br> để hiển thị đẹp hơn
            const formattedReply = data.reply.replace(/\n/g, '<br>');
            loadingLi.querySelector("span").innerHTML = formattedReply;

        } catch (error) {
            console.error(error);
            loadingLi.querySelector("span").innerText = "Lỗi kết nối server. Vui lòng kiểm tra lại!";
            loadingLi.querySelector("span").style.color = "red";
        }
        
        chatBox.scrollTo(0, chatBox.scrollHeight);
    }

    // Xử lý phím Enter
    chatInput.addEventListener("keydown", (e) => {
        if(e.key === "Enter" && !e.shiftKey && window.innerWidth > 800) {
            e.preventDefault();
            handleChat();
        }
    });
</script>