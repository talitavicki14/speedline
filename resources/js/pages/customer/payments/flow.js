const PaymentFlow = {
    config: {
        paymentId: null,
        grandTotal: 0,
        csrf: '',
        timerSeconds: 120,
        type: null,
        bank: null,
    },
    state: {
        timerInterval:     null,
        pollInterval:      null,
        secondsLeft:       0,
        sessionActive:     false,
        isPaymentSuccessful: false,
        currentOrderId:    null,
        lastInstructions:  null,
    },

    init() {
        const el = document.getElementById('payment-init-data');
        if (!el) return;

        this.config = {
            paymentId:    el.dataset.paymentId,
            grandTotal:   el.dataset.grandTotal,
            csrf:         document.querySelector('meta[name="csrf-token"]')?.content,
            timerSeconds: 120,
            type:         el.dataset.type  || null,
            bank:         el.dataset.bank  || null,
        };
        this.state.secondsLeft = this.config.timerSeconds;

        if (this.config.type) {
            this.startPayment(this.config.type, this.config.bank);
        }

        this.setupBackProtection();
    },

    setupBackProtection() {
        window.history.pushState({ protected: true }, "");

        window.addEventListener('popstate', (e) => {
            if (this.state.isPaymentSuccessful) return;

            Swal.fire({
                title: 'Batalkan Pembayaran?',
                text: 'Proses pembayaran Anda sedang aktif. Jika Anda keluar sekarang, transaksi mungkin gagal atau tertunda.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Tetap di Sini dan Selesaikan Pembayaran',
                cancelButtonText: 'Keluar Saja',
                confirmButtonColor: '#0f172a',
                cancelButtonColor: '#cbd5e1',
                reverseButtons: true,
                customClass: {
                    cancelButton: '!text-slate-500 font-medium'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.history.pushState({ protected: true }, "");
                } else {
                    if (this.state.currentOrderId) {
                        this.cancelPaymentSession(true); 
                    } else {
                        window.location.href = `/customer/payments/${this.config.paymentId}`;
                    }
                }
            });
        });

        window.addEventListener('beforeunload', (e) => {
            if (this.state.sessionActive && !this.state.isPaymentSuccessful) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });
    },

    formatRp(n) {
        return 'Rp ' + Number(n).toLocaleString('id-ID');
    },

    copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => this._showToast('✓ Berhasil disalin ke papan klip'));
        } else {
            const ta = document.createElement('textarea');
            ta.value = text; ta.style.position = 'fixed'; ta.style.left = '-9999px';
            document.body.appendChild(ta); ta.focus(); ta.select();
            try { document.execCommand('copy'); this._showToast('✓ Berhasil disalin ke papan klip'); } catch (e) {}
            document.body.removeChild(ta);
        }
    },

    _showToast(msg) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg z-50';
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'opacity 0.3s';
            toast.style.opacity    = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    },

    showStep(step) {
        ['stepChooseMethod', 'stepActivePayment', 'stepPending'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        });
        const target = document.getElementById(step);
        if (target) target.classList.remove('hidden');
    },

    startTimer() {
        this._resetTimerVisuals();

        this.state.secondsLeft = this.config.timerSeconds;
        this.updateTimerUI();

        clearInterval(this.state.timerInterval);
        this.state.timerInterval = setInterval(() => {
            this.state.secondsLeft--;
            this.updateTimerUI();
            if (this.state.secondsLeft <= 0) {
                this.stopTimer();
                this.showStep('stepPending');
            }
        }, 1000);
    },

    _resetTimerVisuals() {
        const bar      = document.getElementById('timerBar');
        const countdown = document.getElementById('countdownDisplay');
        if (bar) {
            bar.className = 'h-full bg-slate-900 transition-all duration-1000';
            bar.style.width = '100%';
        }
        if (countdown) {
            countdown.className = 'font-display font-bold text-2xl text-slate-900';
        }
    },

    updateTimerUI() {
        const countdown = document.getElementById('countdownDisplay');
        const bar       = document.getElementById('timerBar');
        if (!countdown) return;

        const mins = Math.floor(this.state.secondsLeft / 60);
        const secs = this.state.secondsLeft % 60;
        countdown.textContent = `${mins}:${String(secs).padStart(2, '0')}`;

        if (bar) bar.style.width = (this.state.secondsLeft / this.config.timerSeconds * 100) + '%';

        if (this.state.secondsLeft <= 30) {
            bar?.classList.replace('bg-slate-900', 'bg-red-500');
            bar?.classList.replace('bg-amber-400', 'bg-red-500');
            countdown.classList.remove('text-slate-900', 'text-amber-600');
            countdown.classList.add('text-red-600');
        } else if (this.state.secondsLeft <= 60) {
            bar?.classList.replace('bg-slate-900', 'bg-amber-400');
            bar?.classList.replace('bg-red-500', 'bg-amber-400');
            countdown.classList.remove('text-slate-900', 'text-red-600');
            countdown.classList.add('text-amber-600');
        }
    },

    stopTimer() {
        clearInterval(this.state.timerInterval);
        clearInterval(this.state.pollInterval);
        this.state.timerInterval = null;
        this.state.pollInterval  = null;
        this.state.sessionActive = false;
    },

    async startPayment(type, bank) {
        if (this.state.sessionActive) return;
        this.state.sessionActive = true;

        const overlay = document.getElementById('loadingOverlay');
        if (overlay) overlay.classList.remove('hidden');

        try {
            const body = { payment_type: type };
            if (bank) body.bank = bank;

            const res  = await fetch(`/customer/payments/${this.config.paymentId}/digital`, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': this.config.csrf,
                },
                body: JSON.stringify(body),
            });
            const data = await res.json();

            if (overlay) overlay.classList.add('hidden');

            if (!res.ok) {
                this.state.sessionActive = false;
                Swal.fire({ icon: 'error', title: 'Kesalahan Pembayaran', text: data.error || 'Terjadi kesalahan.', confirmButtonColor: '#0f172a' });
                return;
            }

            this.state.currentOrderId = data.order_id || null;
            this.state.lastInstructions = { data, type, bank };

            const labels = {
                bca: 'BCA Virtual Account', bni: 'BNI Virtual Account',
                bri: 'BRI Virtual Account', mandiri: 'Mandiri Bill Payment',
                permata: 'Permata Virtual Account', gopay: 'GoPay', qris: 'QRIS',
            };
            const lbl = document.getElementById('activeMethodLabel');
            if (lbl) lbl.textContent = labels[bank || type] || 'Pembayaran Digital';

            this.renderInstructions(data, type, bank);
            this.showStep('stepActivePayment');
            this.startTimer();
            this.startPolling();

        } catch (e) {
            if (overlay) overlay.classList.add('hidden');
            this.state.sessionActive = false;
            Swal.fire({ icon: 'error', title: 'Kesalahan Jaringan', text: 'Silakan periksa koneksi Anda dan coba lagi.', confirmButtonColor: '#0f172a' });
        }
    },

    async cancelPaymentSession(silent = false) {
        if (!silent) {
            const result = await Swal.fire({
                title:              'Ubah Metode Pembayaran?',
                text:               'Kode pembayaran Anda saat ini akan dibatalkan dan tidak dapat digunakan lagi. Lanjutkan?',
                icon:               'warning',
                showCancelButton:   true,
                confirmButtonColor: '#0f172a',
                cancelButtonColor:  '#e2e8f0',
                confirmButtonText:  'Ya, ubah metode',
                cancelButtonText:   'Tetap di sini',
                customClass: {
                    cancelButton: '!text-slate-700 font-medium',
                },
            });

            if (!result.isConfirmed) return;
        }

        if (this.state.currentOrderId) {
            try {
                await fetch(`/customer/payments/${this.config.paymentId}/cancel`, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': this.config.csrf,
                    },
                    body: JSON.stringify({ order_id: this.state.currentOrderId }),
                });
            } catch (e) {}
        }

        this.stopTimer();        
        window.location.href = `/customer/payments/${this.config.paymentId}`;
    },

    resumePolling() {
        if (!this.state.lastInstructions) {
            this.startPayment(this.config.type, this.config.bank);
            return;
        }

        this.state.sessionActive = true;
        this.showStep('stepActivePayment');
        this.startTimer();
        this.startPolling();
    },

    startPolling() {
        clearInterval(this.state.pollInterval);
        this.state.pollInterval = setInterval(() => this.checkStatus(), 5000);
    },

    async checkStatus() {
        if (!this.state.sessionActive) return;
        try {
            const res  = await fetch(`/customer/payments/${this.config.paymentId}/status`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.status === 'paid') {
                this.stopTimer();
                this.onPaymentSuccess();
            }
        } catch (e) {}
    },

    onPaymentSuccess() {
        this.state.isPaymentSuccessful = true;
        const badge = document.getElementById('statusBadge');
        if (badge) {
            badge.className  = 'inline-flex px-3 py-1.5 rounded-full text-xs font-semibold capitalize bg-emerald-100 text-emerald-700';
            badge.textContent = 'lunas';
        }

        const indicator = document.getElementById('pollingIndicator');
        if (indicator) {
            indicator.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-500"></span><span class="text-emerald-600 font-semibold ml-1">Pembayaran diterima!</span>';
        }

        const instrContent = document.getElementById('paymentInstructionsContent');
        if (instrContent) {
            instrContent.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-check-circle text-emerald-500 text-4xl mb-3"></i>
                    <h3 class="font-semibold text-lg text-slate-900 mb-1">Pembayaran Terkonfirmasi</h3>
                    <p class="text-sm text-slate-500 mb-5">Terima kasih. Catatan layanan Anda telah diperbarui.</p>
                    <a href="/customer/bookings" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm px-6 py-2.5 rounded-xl transition-colors">
                        Lihat Pesanan Saya
                    </a>
                </div>`;
        }

        const cancelBtn = document.getElementById('cancelPaymentBtn');
        if (cancelBtn) cancelBtn.classList.add('hidden');

        const bar       = document.getElementById('timerBar');
        const countdown = document.getElementById('countdownDisplay');
        if (bar) {
            bar.style.width = '100%';
            bar.className   = 'h-full bg-emerald-500 transition-all duration-1000';
        }
        if (countdown) {
            countdown.textContent = '✓';
            countdown.className   = 'font-display font-bold text-2xl text-emerald-600';
        }
    },

    renderInstructions(data, type, bank) {
        const content = document.getElementById('paymentInstructionsContent');
        if (!content) return;
        let html = '';

        const iconPath = `/images/payment/${bank || type}.svg`;
        const iconHtml = `<div class="flex items-center justify-center mx-auto mb-6">
                            <img src="${iconPath}" alt="${bank || type}" class="h-10 w-auto max-w-full object-contain">
                        </div>`;

        if (type === 'bank_transfer' && data.va_number) {
            const bankLabel = bank ? bank.toUpperCase() : 'Bank';
            if (bank === 'mandiri' && data.biller_code) {
                html = `
                    <div class="text-center mb-6">
                        ${iconHtml}
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Mandiri Bill Payment</p>
                    </div>
                    <div class="space-y-3 mb-6">
                        <div class="bg-slate-50 border border-slate-200 rounded-xl px-5 py-3">
                            <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Kode Perusahaan (Biller Code)</p>
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-lg md:text-xl text-slate-900 tracking-wider">${data.biller_code}</span>
                                <button onclick="PaymentFlow.copyToClipboard('${data.biller_code}')"
                                        class="text-xs font-semibold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">Salin</button>
                            </div>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl px-5 py-3">
                            <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Nomor VA (Bill Key)</p>
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-lg md:text-xl text-slate-900 tracking-wider">${data.va_number}</span>
                                <button onclick="PaymentFlow.copyToClipboard('${data.va_number}')"
                                        class="text-xs font-semibold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">Salin</button>
                            </div>
                        </div>
                    </div>`;
            } else {
                html = `
                    <div class="text-center mb-6">
                        ${iconHtml}
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">${bankLabel} Virtual Account</p>
                    </div>
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl px-5 py-4 mb-6">
                        <span class="font-mono font-bold text-lg md:text-2xl text-slate-900 tracking-wider md:tracking-widest">${data.va_number}</span>
                        <button onclick="PaymentFlow.copyToClipboard('${data.va_number}')"
                                class="text-xs font-semibold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-colors">Salin</button>
                    </div>`;
            }
            html += `<div class="text-center"><p class="text-sm text-slate-500">Transfer tepat <strong class="text-slate-900">${this.formatRp(this.config.grandTotal)}</strong> ke rekening ini.</p></div>`;

        } else if ((type === 'gopay' || type === 'qris') && data.qr_code_url) {
            html = `
                <div class="text-center mb-6">
                    ${iconHtml}
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Scan dengan ${type === 'gopay' ? 'GoPay' : 'aplikasi pembayaran apa saja'}</p>
                </div>
                <div class="flex justify-center mb-8">
                    <div class="p-4 bg-white border border-slate-200 rounded-2xl shadow-sm">
                        <img src="${data.qr_code_url}" alt="QR Code" class="w-48 h-48 block mx-auto">
                    </div>
                </div>
                ${data.deeplink_url ? `
                <a href="${data.deeplink_url}" target="_blank" class="w-full flex items-center justify-center gap-3 bg-[#00AED6] hover:bg-[#0095B7] text-white font-bold text-sm py-4 rounded-2xl transition-all shadow-lg shadow-blue-100 mb-6">
                    <i class="fas fa-mobile-alt"></i> Buka ${type === 'gopay' ? 'GoPay' : 'Aplikasi Pembayaran'}
                </a>` : ''}
                <p class="text-xs text-slate-400 text-center">Halaman ini akan diperbarui secara otomatis setelah pembayaran dikonfirmasi.</p>`;
        } else {
            html = `
                <div class="text-center py-4">
                    ${iconHtml}
                    <p class="text-sm text-slate-500">ID Pesanan: <strong>${data.order_id}</strong><br>Selesaikan pembayaran melalui aplikasi perbankan Anda.</p>
                </div>`;
        }

        content.innerHTML = html;
    },
};

window.PaymentFlow = PaymentFlow;

document.addEventListener('DOMContentLoaded', () => PaymentFlow.init());

window.cancelPaymentSession = () => PaymentFlow.cancelPaymentSession();
window.resumePolling = () => PaymentFlow.resumePolling();
