<!-- Floating Chat Widget -->
<div id="support-chat-wrapper" class="fixed bottom-6 right-24 z-[9999] font-sans">
    <style>
        /* Custom styling for support chat container */
        .chat-scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .chat-scrollbar-hide {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        .bot-bubble-style {
            position: relative;
            animation: bubbleFadeIn 0.25s ease-out;
        }
        @keyframes bubbleFadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <!-- Chat Launcher Button -->
    <button id="chat-launcher" onclick="toggleChatWindow()" class="w-14 h-14 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-full flex items-center justify-center text-white shadow-[0_8px_24px_rgba(37,99,235,0.25)] hover:shadow-[0_8px_30px_rgba(37,99,235,0.35)] hover:scale-105 active:scale-95 transition-all duration-300 relative group cursor-pointer">
        <span class="material-symbols-outlined text-[26px]">support_agent</span>
        <!-- Tooltip -->
        <span class="absolute right-16 bg-slate-900/90 backdrop-blur-md text-white text-xs font-semibold px-3 py-1.5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none whitespace-nowrap shadow-md">
            Trợ giúp trực tuyến
        </span>
        <!-- Online Status Dot -->
        <span class="absolute top-0.5 right-0.5 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></span>
    </button>

    <!-- Chat Window Container -->
    <div id="chat-window" class="absolute bottom-[70px] right-0 w-[360px] h-[500px] bg-white rounded-2xl shadow-[0_12px_40px_rgba(15,23,42,0.15)] border border-slate-100/80 flex flex-col overflow-hidden opacity-0 scale-95 pointer-events-none origin-bottom-right transition-all duration-300">
        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-4 flex items-center justify-between text-white shadow-sm shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white/10 ring-2 ring-white/10 flex items-center justify-center relative shrink-0">
                    <span class="material-symbols-outlined text-[20px] text-white">smart_toy</span>
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 rounded-full border border-indigo-600"></span>
                </div>
                <div>
                    <h3 class="font-bold text-sm leading-tight tracking-wide">Hỗ Trợ Viên 24/7</h3>
                    <p class="text-[10px] text-blue-100/90 font-medium">Trả lời tự động siêu tốc</p>
                </div>
            </div>
            <button onclick="toggleChatWindow()" class="w-8 h-8 rounded-full hover:bg-white/15 flex items-center justify-center transition-colors cursor-pointer shrink-0">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <!-- Chat Messages Body -->
        <div id="chat-messages" class="flex-grow p-5 overflow-y-auto space-y-4 bg-[#f8fafc] text-[13px] leading-relaxed text-slate-700">
            <!-- Messages will be dynamically rendered here -->
        </div>

        <!-- Chat Quick Suggestions (Horizontal Scroll) -->
        <div id="chat-suggestions" class="p-3 bg-white border-t border-slate-100/80 flex gap-2 overflow-x-auto whitespace-nowrap select-none chat-scrollbar-hide shrink-0">
            <button onclick="sendQuickMessage('🛍️ Cách mua hàng?')" class="px-3.5 py-1.5 bg-slate-50 hover:bg-blue-50/50 hover:text-blue-600 rounded-full text-slate-600 text-xs font-semibold border border-slate-200/60 transition-all duration-200 cursor-pointer shrink-0">🛍️ Cách mua hàng?</button>
            <button onclick="sendQuickMessage('📝 Cách đăng tin?')" class="px-3.5 py-1.5 bg-slate-50 hover:bg-blue-50/50 hover:text-blue-600 rounded-full text-slate-600 text-xs font-semibold border border-slate-200/60 transition-all duration-200 cursor-pointer shrink-0">📝 Cách đăng tin?</button>
            <button onclick="sendQuickMessage('💳 Phương thức thanh toán?')" class="px-3.5 py-1.5 bg-slate-50 hover:bg-blue-50/50 hover:text-blue-600 rounded-full text-slate-600 text-xs font-semibold border border-slate-200/60 transition-all duration-200 cursor-pointer shrink-0">💳 Thanh toán?</button>
            <button onclick="sendQuickMessage('📞 Liên hệ Admin?')" class="px-3.5 py-1.5 bg-slate-50 hover:bg-blue-50/50 hover:text-blue-600 rounded-full text-slate-600 text-xs font-semibold border border-slate-200/60 transition-all duration-200 cursor-pointer shrink-0">📞 Liên hệ Admin?</button>
        </div>

        <!-- Chat Input Footer -->
        <form id="chat-form" onsubmit="handleChatSubmit(event)" class="p-3 bg-white border-t border-slate-100/80 flex items-center gap-2 shrink-0">
            <input id="chat-input" type="text" placeholder="Nhập tin nhắn hỗ trợ..." class="flex-grow px-4 py-2.5 text-xs bg-slate-50 border border-slate-200/60 rounded-full focus:outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 transition-all">
            <button type="submit" class="w-9 h-9 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center transition-colors shadow-md shadow-blue-500/10 cursor-pointer shrink-0">
                <span class="material-symbols-outlined text-[18px] rotate-[-45deg] relative top-[-1px] left-[1px]">send</span>
            </button>
        </form>
    </div>
</div>

<script>
    const chatMessagesEl = document.getElementById('chat-messages');
    const chatWindowEl = document.getElementById('chat-window');
    const chatInputEl = document.getElementById('chat-input');
    let isChatOpen = false;
    let botGreetingsSent = false;

    // Toggle Open/Close
    function toggleChatWindow() {
        isChatOpen = !isChatOpen;
        if (isChatOpen) {
            chatWindowEl.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
            if (!botGreetingsSent) {
                initBotGreetings();
            }
            setTimeout(() => chatInputEl.focus(), 300);
        } else {
            chatWindowEl.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
        }
    }

    // Insert Chat Bubble
    function appendMessage(text, isBot = false, isTyping = false) {
        if (!chatMessagesEl) return;
        
        // Remove typing indicator if any before adding a new real message
        if (!isTyping) {
            const typingIndicator = document.getElementById('bot-typing-indicator');
            if (typingIndicator) typingIndicator.remove();
        }

        const msgDiv = document.createElement('div');
        msgDiv.className = `flex gap-2.5 max-w-[85%] bot-bubble-style ${isBot ? 'mr-auto' : 'ml-auto flex-row-reverse'}`;
        if (isTyping) {
            msgDiv.id = 'bot-typing-indicator';
        }

        const avatar = isBot 
            ? `<div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 shadow-sm">
                    <span class="material-symbols-outlined text-[16px] text-slate-600 font-semibold">smart_toy</span>
               </div>`
            : ``;

        const bubbleBg = isBot 
            ? 'bg-white text-slate-700 border border-slate-100 rounded-[18px] rounded-tl-sm shadow-sm'
            : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-[18px] rounded-tr-sm shadow-sm';

        const content = isTyping 
            ? `<div class="flex items-center gap-1.5 py-3 px-4">
                 <span class="w-2.5 h-2.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
                 <span class="w-2.5 h-2.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                 <span class="w-2.5 h-2.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
               </div>`
            : `<div class="py-2.5 px-4 font-medium">${text}</div>`;

        msgDiv.innerHTML = `
            ${avatar}
            <div class="${bubbleBg}">
                ${content}
            </div>
        `;
        
        chatMessagesEl.appendChild(msgDiv);
        chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
    }

    // Bot First Greetings
    function initBotGreetings() {
        botGreetingsSent = true;
        appendMessage("Xin chào! 👋 Cảm ơn bạn đã ghé thăm Chợ Thanh Lý.");
        appendMessage("Tôi là trợ lý ảo chăm sóc khách hàng 24/7. Tôi có thể giúp gì cho bạn hôm nay? Bạn có thể chọn nhanh các gợi ý phía dưới nhé! 👇", true);
    }

    // Auto replies based on keywords
    function getBotReply(userMsg) {
        const text = userMsg.toLowerCase().trim();
        
        if (text.includes('mua') || text.includes('giỏ') || text.includes('đặt hàng')) {
            return "🛍️ **Cách mua sản phẩm:**<br>1. Click trực tiếp vào sản phẩm bạn mong muốn để xem chi tiết.<br>2. Chọn số lượng sản phẩm.<br>3. Bấm **'Thêm vào giỏ hàng'** để tiếp tục lướt xem hàng hoặc bấm **'Mua ngay'** để chuyển đến giao diện Thanh toán và nhập địa chỉ.";
        }
        if (text.includes('đăng') || text.includes('bán') || text.includes('tin')) {
            return "📝 **Cách đăng tin thanh lý:**<br>1. Hãy chắc chắn bạn đã đăng nhập tài khoản.<br>2. Click vào nút màu cam **'Đăng tin'** góc trên bên phải màn hình.<br>3. Điền đầy đủ thông tin (tên sản phẩm, giá bán, danh mục, tình trạng sử dụng, bảo hành, hình ảnh...).<br>4. Bấm **'Đăng tin'** là hoàn tất!";
        }
        if (text.includes('thanh toán') || text.includes('chuyển khoản') || text.includes('tiền') || text.includes('bank')) {
            return "💳 **Phương thức thanh toán hỗ trợ:**<br>Dự án hỗ trợ 2 hình thức thanh toán khi thực hiện đặt đơn hàng:<br>1. Giao hàng nhận tiền (COD).<br>2. Chuyển khoản trực tiếp qua ngân hàng.";
        }
        if (text.includes('liên hệ') || text.includes('admin') || text.includes('gặp') || text.includes('hỗ trợ')) {
            return "📞 **Kênh liên hệ trực tiếp đến Ban quản trị:**<br>- **Email hỗ trợ:** admin@c2c.vn<br>- **Đường dây nóng:** 0901 234 567<br>- **Thời gian hỗ trợ:** 8:00 - 22:00 tất cả các ngày trong tuần.";
        }
        if (text.includes('chào') || text.includes('hello') || text.includes('hi') || text.includes('alo')) {
            return "Xin chào! Trợ lý ảo Chợ Thanh Lý rất vui được hỗ trợ bạn. Bạn đang cần hỗ trợ thông tin gì ạ?";
        }
        
        return "Cảm ơn thông tin từ bạn! Yêu cầu hỗ trợ của bạn đã được chuyển đến bộ phận xử lý. Nếu cần phản hồi gấp, bạn có thể gọi hotline **0901 234 567** hoặc gửi thư về địa chỉ **admin@c2c.vn** nhé! ❤️";
    }

    // Submit Custom Message
    function handleChatSubmit(e) {
        e.preventDefault();
        const text = chatInputEl.value.trim();
        if (!text) return;

        // Append user message
        appendMessage(text, false);
        chatInputEl.value = '';

        // Simulate Bot response
        simulateBotResponse(text);
    }

    // Trigger Quick Suggestions
    function sendQuickMessage(text) {
        appendMessage(text, false);
        simulateBotResponse(text);
    }

    // Bot Typing & Reply Simulation
    function simulateBotResponse(userMsg) {
        // Show Typing Indicator
        appendMessage('', true, true);

        // Delayed reply (1 second delay for realistic feel)
        setTimeout(() => {
            const reply = getBotReply(userMsg);
            appendMessage(reply, true);
        }, 1000);
    }
</script>
