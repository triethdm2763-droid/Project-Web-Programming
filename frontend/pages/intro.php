<?php
require_once __DIR__ . '/../components/session.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Chào mừng | Chợ Thanh Lý</title>
    <?php include '../components/header.php'; ?>
    <style>
        .marquee-wrapper {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            background: #f8fafc; /* slate-50 */
        }
        
        .marquee-row {
            display: flex;
            overflow: hidden;
            width: 100vw;
            position: relative;
            padding: 10px 0;
        }

        .marquee-content {
            display: flex;
            width: max-content;
            gap: 1.5rem;
            padding-right: 1.5rem;
        }

        .marquee-content.left {
            animation: scroll-left 40s linear infinite;
        }

        .marquee-content.right {
            animation: scroll-right 40s linear infinite;
        }

        @keyframes scroll-left {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        @keyframes scroll-right {
            0% { transform: translateX(-50%); }
            100% { transform: translateX(0); }
        }

        .marquee-item {
            width: 200px;
            height: 200px;
            flex-shrink: 0;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        @media (min-width: 768px) {
            .marquee-item {
                width: 280px;
                height: 280px;
            }
        }

        .marquee-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Progress Bar */
        #progress-bar {
            width: 0%;
            transition: width 0.1s linear;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen overflow-hidden relative font-body-md">

    <!-- Background: 3 Marquee Rows -->
    <div class="absolute inset-0 z-0 marquee-wrapper" id="marquee-container">
        <!-- Rows will be injected by JS -->
    </div>

    <!-- Lớp phủ mờ đã được gỡ bỏ theo yêu cầu -->

    <!-- Khung trung tâm (Center Overlay) - Kiểu gương (Glassmorphism) -->
    <div class="absolute inset-0 z-20 flex items-center justify-center p-4">
        <div class="bg-white/40 backdrop-blur-xl border border-white/50 rounded-[32px] p-10 md:p-14 max-w-lg w-full text-center shadow-[0_8px_32px_0_rgba(31,38,135,0.15)] transform hover:scale-[1.02] transition-transform duration-500">
            
            <div class="w-20 h-20 bg-white/50 backdrop-blur-md rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm border border-white/60 p-2 overflow-hidden">
                <img src="/frontend/assets/images/favicon.png?v=<?= time() ?>" alt="Logo" class="w-full h-full object-contain drop-shadow-sm">
            </div>
            
            <h1 class="text-3xl font-black text-slate-800 mb-3 tracking-tight">Chợ Thanh Lý</h1>
            <p class="text-slate-500 text-sm mb-10 leading-relaxed">
                Nền tảng mua bán đồ cũ độc bản.<br>Trải nghiệm săn sale thần tốc và an toàn.
            </p>

            <a href="/frontend/pages/home/index.php" id="enter-btn" class="inline-block w-full bg-[#0066cc] hover:bg-[#0052a3] text-white font-bold text-lg py-4 rounded-xl shadow-lg shadow-blue-500/20 transition-all hover:-translate-y-1 active:translate-y-0 relative overflow-hidden group">
                <span class="relative z-10">Khám Phá Ngay</span>
                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
            </a>

            <!-- Progress bar container -->
            <div class="mt-6">
                <p class="text-xs text-slate-400 mb-2 font-medium">Tự động chuyển trang sau <span id="countdown-text" class="text-slate-600 font-bold">5</span> giây...</p>
                <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                    <div id="progress-bar" class="h-full bg-[#0066cc] rounded-full"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                // Fetch at least 30 products to fill the rows nicely
                const response = await fetch('/backend/public/index.php/api/products?limit=30');
                const result = await response.json();
                
                let products = [];
                if (result.data && result.data.length > 0) {
                    products = result.data;
                } else {
                    // Fallback placeholders if API fails or no products
                    products = Array(15).fill({ Image: 'placeholder.png' });
                }

                // Shuffle array randomly
                products.sort(() => 0.5 - Math.random());

                const container = document.getElementById('marquee-container');
                
                // We create 3 rows
                for (let r = 0; r < 3; r++) {
                    const rowDiv = document.createElement('div');
                    rowDiv.className = 'marquee-row';
                    
                    const contentDiv = document.createElement('div');
                    // Row 0, 2 move left. Row 1 moves right.
                    contentDiv.className = 'marquee-content ' + (r % 2 === 0 ? 'left' : 'right');
                    
                    // Take a chunk of products for this row
                    // If we don't have enough, we just reuse the whole array
                    let chunk = products;
                    if (products.length >= 15) {
                        const start = (r * 10) % products.length;
                        chunk = products.slice(start, start + 10);
                        if (chunk.length < 10) chunk = chunk.concat(products.slice(0, 10 - chunk.length));
                    }

                    // Build HTML for items
                    let itemsHtml = chunk.map(product => {
                        const imgUrl = (product.Image && (product.Image.startsWith('http://') || product.Image.startsWith('https://'))) 
                            ? product.Image 
                            : '/backend/uploads/products/' + (product.Image || 'placeholder.png');
                        return `
                            <div class="marquee-item">
                                <img src="${imgUrl}" onerror="this.src='/frontend/assets/images/placeholder.png'" alt="">
                            </div>
                        `;
                    }).join('');

                    // Duplicate items to create infinite scroll effect
                    // We need enough items so that 50% width is wider than the screen
                    itemsHtml = itemsHtml + itemsHtml + itemsHtml + itemsHtml; 
                    
                    contentDiv.innerHTML = itemsHtml;
                    rowDiv.appendChild(contentDiv);
                    container.appendChild(rowDiv);
                }

            } catch (error) {
                console.error("Không tải được ảnh sản phẩm cho Intro:", error);
            }

            // Logic đếm ngược 5 giây
            const totalTime = 5000;
            const interval = 50; 
            let elapsedTime = 0;
            const progressBar = document.getElementById('progress-bar');
            const countdownText = document.getElementById('countdown-text');

            const timer = setInterval(() => {
                elapsedTime += interval;
                const percentage = (elapsedTime / totalTime) * 100;
                progressBar.style.width = percentage + '%';

                const secondsLeft = Math.ceil((totalTime - elapsedTime) / 1000);
                countdownText.textContent = secondsLeft;

                if (elapsedTime >= totalTime) {
                    clearInterval(timer);
                    window.location.href = '/frontend/pages/home/index.php';
                }
            }, interval);

            document.getElementById('enter-btn').addEventListener('click', (e) => {
                e.preventDefault();
                clearInterval(timer);
                window.location.href = '/frontend/pages/home/index.php';
            });
        });
    </script>
</body>
</html>
