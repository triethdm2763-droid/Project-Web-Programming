/**
 * Custom Premium UI Helpers for Chợ Cũ Marketplace
 * Replaces native browser alert() and confirm() with beautiful, responsive, and animated components.
 */

// Initialize styling & containers
(function() {
    const style = document.createElement('style');
    style.innerHTML = `
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }
        .toast-card {
            pointer-events: auto;
            min-width: 280px;
            max-width: 380px;
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            transform: translateX(120%);
            opacity: 0;
            transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .toast-card.show {
            transform: translateX(0);
            opacity: 1;
        }
        .toast-card.hide {
            transform: translateY(-20px);
            opacity: 0;
        }
    `;
    document.head.appendChild(style);

    // Create Toast container if not exists
    document.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('global-toast-container')) {
            const container = document.createElement('div');
            container.id = 'global-toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
    });
})();

/**
 * Show a sleek Toast Notification in the top-right corner
 * @param {string} message 
 * @param {'success'|'error'|'warning'|'info'} type 
 */
window.showToast = function(message, type = 'success') {
    const container = document.getElementById('global-toast-container') || document.body;
    
    // Create card element
    const toast = document.createElement('div');
    toast.className = 'toast-card';

    let icon = 'check_circle';
    let iconColor = 'text-green-500';
    let borderLeft = 'border-l-4 border-green-500';
    
    if (type === 'error') {
        icon = 'error';
        iconColor = 'text-red-500';
        borderLeft = 'border-l-4 border-red-500';
    } else if (type === 'warning') {
        icon = 'warning';
        iconColor = 'text-amber-500';
        borderLeft = 'border-l-4 border-amber-500';
    } else if (type === 'info') {
        icon = 'info';
        iconColor = 'text-blue-500';
        borderLeft = 'border-l-4 border-blue-500';
    }

    toast.className = `toast-card ${borderLeft} flex items-center gap-3`;
    toast.innerHTML = `
        <span class="material-symbols-outlined ${iconColor} text-[24px]">${icon}</span>
        <div class="flex-grow pr-2">
            <p class="text-sm font-medium text-gray-800">${message}</p>
        </div>
        <button class="text-gray-400 hover:text-gray-600 transition-colors flex items-center justify-center p-0.5 rounded-full hover:bg-gray-100" onclick="this.parentElement.remove()">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    `;

    container.appendChild(toast);

    // Trigger transition
    setTimeout(() => toast.classList.add('show'), 10);

    // Auto-remove
    setTimeout(() => {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 355);
    }, 3200);
};

/**
 * Show a beautiful modal Alert dialog (Returns Promise)
 * @param {string} title 
 * @param {string} message 
 * @param {'success'|'error'|'warning'|'info'} type 
 * @returns {Promise<boolean>}
 */
window.showAlert = function(title, message, type = 'success') {
    return new Promise((resolve) => {
        // Create elements
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 opacity-0 transition-opacity duration-300 pointer-events-none';
        
        let icon = 'check_circle';
        let iconBg = 'bg-green-50 text-green-600';
        let btnBg = 'bg-[#004ac6] hover:bg-opacity-95'; // brand primary
        
        if (type === 'error') {
            icon = 'error';
            iconBg = 'bg-red-50 text-red-600';
            btnBg = 'bg-red-600 hover:bg-red-700';
        } else if (type === 'warning') {
            icon = 'warning';
            iconBg = 'bg-amber-50 text-amber-600';
            btnBg = 'bg-amber-600 hover:bg-amber-700';
        } else if (type === 'info') {
            icon = 'info';
            iconBg = 'bg-blue-50 text-blue-600';
            btnBg = 'bg-blue-600 hover:bg-blue-700';
        }

        const modal = document.createElement('div');
        modal.className = 'bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl flex flex-col items-center text-center transform scale-95 transition-all duration-300 pointer-events-auto';
        modal.innerHTML = `
            <div class="w-14 h-14 rounded-full ${iconBg} flex items-center justify-center">
                <span class="material-symbols-outlined text-[32px]">${icon}</span>
            </div>
            <h3 class="text-gray-900 font-bold text-lg mt-4 mb-2">${title}</h3>
            <p class="text-gray-500 text-sm leading-relaxed">${message}</p>
            <button id="alert-confirm-btn" class="w-full ${btnBg} text-white py-3 rounded-xl font-semibold shadow-md active:scale-95 transition-all mt-6">
                Đồng ý
            </button>
        `;

        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        // Show transition
        setTimeout(() => {
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.remove('scale-95');
        }, 10);

        // Close logic
        const close = () => {
            overlay.classList.add('opacity-0', 'pointer-events-none');
            modal.classList.add('scale-95');
            setTimeout(() => {
                overlay.remove();
                resolve(true);
            }, 300);
        };

        modal.querySelector('#alert-confirm-btn').addEventListener('click', close);
    });
};

/**
 * Show a beautiful modal Confirm dialog (Returns Promise resolving to true/false)
 * @param {string} title 
 * @param {string} message 
 * @param {'success'|'error'|'warning'|'info'} type 
 * @returns {Promise<boolean>}
 */
window.showConfirm = function(title, message, type = 'warning') {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 opacity-0 transition-opacity duration-300 pointer-events-none';
        
        let icon = 'help';
        let iconBg = 'bg-amber-50 text-amber-600';
        let btnBg = 'bg-[#004ac6] hover:bg-opacity-95'; // brand primary
        
        if (type === 'error') {
            icon = 'error';
            iconBg = 'bg-red-50 text-red-600';
            btnBg = 'bg-red-600 hover:bg-red-700';
        } else if (type === 'success') {
            icon = 'check_circle';
            iconBg = 'bg-green-50 text-green-600';
            btnBg = 'bg-green-600 hover:bg-green-700';
        }

        const modal = document.createElement('div');
        modal.className = 'bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl flex flex-col items-center text-center transform scale-95 transition-all duration-300 pointer-events-auto';
        modal.innerHTML = `
            <div class="w-14 h-14 rounded-full ${iconBg} flex items-center justify-center">
                <span class="material-symbols-outlined text-[32px]">${icon}</span>
            </div>
            <h3 class="text-gray-900 font-bold text-lg mt-4 mb-2">${title}</h3>
            <p class="text-gray-500 text-sm leading-relaxed">${message}</p>
            <div class="flex gap-3 w-full mt-6">
                <button id="confirm-cancel-btn" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-all">
                    Hủy bỏ
                </button>
                <button id="confirm-ok-btn" class="flex-1 py-3 ${btnBg} text-white rounded-xl font-semibold shadow-md active:scale-95 transition-all">
                    Xác nhận
                </button>
            </div>
        `;

        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        // Show transition
        setTimeout(() => {
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.remove('scale-95');
        }, 10);

        // Close handlers
        const handleCancel = () => {
            overlay.classList.add('opacity-0', 'pointer-events-none');
            modal.classList.add('scale-95');
            setTimeout(() => {
                overlay.remove();
                resolve(false);
            }, 300);
        };

        const handleOk = () => {
            overlay.classList.add('opacity-0', 'pointer-events-none');
            modal.classList.add('scale-95');
            setTimeout(() => {
                overlay.remove();
                resolve(true);
            }, 300);
        };

        modal.querySelector('#confirm-cancel-btn').addEventListener('click', handleCancel);
        modal.querySelector('#confirm-ok-btn').addEventListener('click', handleOk);
    });
};
