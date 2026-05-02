document.addEventListener('DOMContentLoaded', () => {
    const resendBtn = document.getElementById('resendBtn');
    const btnText = document.getElementById('btnText');
    const resendForm = document.getElementById('resendForm');
    const body = document.body;
    const cooldownKey = 'speedline_auth_resend_cooldown';

    if (body.dataset.verificationSent === 'true') {
        if (window.Swal) {
            Swal.fire({
                icon: 'success',
                title: 'Tautan Terkirim!',
                text: 'Tautan verifikasi baru telah dikirim ke alamat email Anda.',
                confirmButtonColor: '#0f172a',
            });
        }
        
        const expiry = Date.now() + (60 * 1000);
        localStorage.setItem(cooldownKey, expiry.toString());
    }

    const checkCooldown = () => {
        const savedTime = localStorage.getItem(cooldownKey);
        if (savedTime) {
            const remaining = Math.ceil((parseInt(savedTime) - Date.now()) / 1000);
            if (remaining > 0) {
                startCountdown(remaining);
            } else {
                localStorage.removeItem(cooldownKey);
            }
        }
    };

    checkCooldown();

    if (resendForm) {
        resendForm.addEventListener('submit', () => {
            resendBtn.disabled = true;
            resendBtn.classList.add('opacity-80', 'cursor-not-allowed');
            btnText.textContent = 'Mengirim...';
            
            const icon = resendBtn.querySelector('i');
            if (icon) icon.className = 'fas fa-circle-notch fa-spin text-xs';

            const expiry = Date.now() + (60 * 1000);
            localStorage.setItem(cooldownKey, expiry.toString());
        });
    }

    function startCountdown(seconds) {
        if (!resendBtn || !btnText) return;
        
        resendBtn.disabled = true;
        resendBtn.classList.add('opacity-80', 'cursor-not-allowed');
        let timeLeft = seconds;
        
        const icon = resendBtn.querySelector('i');
        if (icon) icon.classList.add('hidden');

        const timer = setInterval(() => {
            timeLeft--;
            btnText.textContent = `Tunggu ${timeLeft} detik...`;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                resendBtn.disabled = false;
                resendBtn.classList.remove('opacity-80', 'cursor-not-allowed');
                btnText.textContent = 'Kirim Ulang Tautan';
                if (icon) {
                    icon.className = 'fas fa-paper-plane text-xs';
                    icon.classList.remove('hidden');
                }
                localStorage.removeItem(cooldownKey);
            }
        }, 1000);
        
        btnText.textContent = `Tunggu ${timeLeft} detik...`;
    }
});
