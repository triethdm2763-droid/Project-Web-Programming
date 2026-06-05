<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Kênh Người Bán - Chợ Cũ</title>
    <?php include '../../components/header.php'; ?>
</head>
<body class="bg-surface font-body-md text-on-surface min-h-screen flex flex-col">

    <?php include '../../components/navbar.php'; ?>

    <main class="max-w-container-max mx-auto px-gutter py-8 flex-grow w-full space-y-6">
        <div class="max-w-5xl mx-auto px-4 py-0 space-y-6">
        
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
                <div class="w-16 h-16 bg-gradient-to-tr from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-md shadow-blue-100 shrink-0">
                    QA
                </div>
                <div>
                    <div class="flex items-center gap-2 justify-center sm:justify-start">
                        <h2 class="text-xl font-bold text-slate-900">Cửa Hàng Quốc Anh</h2>
                        <span class="bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Seller Pro</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1 flex items-center justify-center sm:justify-start gap-1">
                        <span class="material-symbols-outlined text-xs">storefront</span> Hệ thống bán hàng & thanh lý chính chủ
                    </p>
                </div>
            </div>
            
            <button class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-xl text-sm transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-600/10 active:scale-95">
                <span class="material-symbols-outlined text-lg">add_box</span> Đăng tin thanh lý mới
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:border-blue-500/30 transition-all">
                <div class="space-y-1">
                    <span class="text-xs text-slate-400 font-medium uppercase tracking-wider block">Doanh Thu Tạm Tính</span>
                    <h4 class="text-2xl font-bold text-slate-900 tracking-tight">24.500.000đ</h4>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">payments</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:border-blue-500/30 transition-all">
                <div class="space-y-1">
                    <span class="text-xs text-slate-400 font-medium uppercase tracking-wider block">Đơn Hàng Đã Giao</span>
                    <h4 class="text-2xl font-bold text-slate-900 tracking-tight">156 đơn</h4>
                </div>
                <div class="p-3 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">local_shipping</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex border-b border-slate-100 bg-slate-50/50 px-4">
                <button onclick="switchSellerTab('available')" id="tab-btn-available" class="seller-tab-btn py-4 px-5 font-bold text-sm border-b-2 border-blue-600 text-blue-600 transition-all">
                    Đang bán (12)
                </button>
                <button onclick="switchSellerTab('pending')" id="tab-btn-pending" class="seller-tab-btn py-4 px-5 font-bold text-sm border-b-2 border-transparent text-slate-400 hover:text-slate-800 transition-all">
                    Chờ duyệt (2)
                </button>
                <button onclick="switchSellerTab('sold')" id="tab-btn-sold" class="seller-tab-btn py-4 px-5 font-bold text-sm border-b-2 border-transparent text-slate-400 hover:text-slate-800 transition-all">
                    Đã bán (45)
                </button>
            </div>

            <div id="seller-products-list" class="divide-y divide-slate-50 p-2">
                </div>
        </div>

        </div>
    </main>

    <?php include '../../components/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            switchSellerTab('available');
        });

        function switchSellerTab(status) {
            document.querySelectorAll('.seller-tab-btn').forEach(btn => {
                btn.classList.remove('border-blue-600', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-slate-400');
            });
            const activeBtn = document.getElementById(`tab-btn-${status}`);
            if (activeBtn) {
                activeBtn.classList.remove('border-transparent', 'text-slate-400');
                activeBtn.classList.add('border-blue-600', 'text-blue-600');
            }

            const container = document.getElementById('seller-products-list');
        

            const list = sampleSellerData[status] || [];
            if (list.length === 0) {
                container.innerHTML = `<div class="text-center text-slate-400 py-12 text-sm">Không có sản phẩm nào trong mục này.</div>`;
                return;
            }

            container.innerHTML = list.map(p => `
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 gap-4 hover:bg-slate-50/60 transition-all rounded-xl">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-14 h-14 bg-slate-50 border border-slate-100 rounded-xl overflow-hidden shrink-0 flex items-center justify-center text-slate-300">
                            <span class="material-symbols-outlined text-2xl">image</span>
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-semibold text-slate-800 text-sm truncate">${p.name}</h4>
                            <p class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">schedule</span> Đăng lúc: ${p.time}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end gap-6 shrink-0">
                        <div class="text-left sm:text-right">
                            <span class="text-sm font-bold text-orange-600 block">${new Intl.NumberFormat('vi-VN', {style:'currency', currency:'VND'}).format(p.price)}</span>
                            <span class="inline-block text-[10px] font-bold border px-2 py-0.5 rounded-full mt-1 ${p.color}">${p.label}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors flex items-center" title="Sửa tin"><span class="material-symbols-outlined text-[20px]">edit</span></button>
                            <button class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors flex items-center" title="Xóa tin"><span class="material-symbols-outlined text-[20px]">delete</span></button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    </script>
</body>
</html>