window.closeAllDropdowns = function() {
    const accountPanel = document.getElementById('accountPanel');
    const accountChevron = document.getElementById('accountChevron');
    const notifPanel = document.getElementById('notifPanel');
    const customerAccountPanel = document.getElementById('customerAccountPanel');
    const customerChevron = document.getElementById('customerChevron');

    if (accountPanel) accountPanel.classList.add('hidden');
    if (accountChevron) accountChevron.classList.remove('rotate-180');
    if (notifPanel) notifPanel.classList.add('hidden');
    if (customerAccountPanel) customerAccountPanel.classList.add('hidden');
    if (customerChevron) customerChevron.classList.remove('rotate-180');

    if (window.CustomSelect && typeof window.CustomSelect.closeAll === 'function') {
        window.CustomSelect.closeAll();
    }
    if (window.CustomDatePicker && typeof window.CustomDatePicker.closeAll === 'function') {
        window.CustomDatePicker.closeAll();
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const accountToggle = document.getElementById('accountToggle');
    const accountPanel = document.getElementById('accountPanel');
    const accountChevron = document.getElementById('accountChevron');

    if (accountToggle && accountPanel && accountChevron) {
        accountToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isHidden = accountPanel.classList.contains('hidden');
            window.closeAllDropdowns();
            if (isHidden) {
                accountPanel.classList.remove('hidden');
                accountChevron.classList.add('rotate-180');
            }
        });

        document.addEventListener('click', (e) => {
            if (!accountToggle.contains(e.target) && !accountPanel.contains(e.target)) {
                accountPanel.classList.add('hidden');
                accountChevron.classList.remove('rotate-180');
            }
        });
    }

    const notifBtn = document.getElementById('notifBtn');
    const notifPanel = document.getElementById('notifPanel');

    if (notifBtn && notifPanel) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isHidden = notifPanel.classList.contains('hidden');
            window.closeAllDropdowns();
            if (isHidden) notifPanel.classList.remove('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!notifBtn.contains(e.target) && !notifPanel.contains(e.target)) {
                notifPanel.classList.add('hidden');
            }
        });
    }

    const customerAccountBtn = document.getElementById('customerAccountBtn');
    const customerAccountPanel = document.getElementById('customerAccountPanel');
    const customerChevron = document.getElementById('customerChevron');

    if (customerAccountBtn && customerAccountPanel && customerChevron) {
        customerAccountBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isHidden = customerAccountPanel.classList.contains('hidden');
            window.closeAllDropdowns();
            if (isHidden) {
                customerAccountPanel.classList.remove('hidden');
                customerChevron.classList.add('rotate-180');
            }
        });

        document.addEventListener('click', (e) => {
            if (!customerAccountBtn.contains(e.target) && !customerAccountPanel.contains(e.target)) {
                customerAccountPanel.classList.add('hidden');
                customerChevron.classList.remove('rotate-180');
            }
        });
    }

    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    const openSidebar = () => {
        if (!sidebar) return;
        sidebar.classList.remove('-translate-x-full');
        sidebarOverlay?.classList.remove('hidden');
        setTimeout(() => {
            sidebarOverlay?.classList.remove('opacity-0');
        }, 10);
        document.body.style.overflow = 'hidden';
    };

    const closeSidebar = () => {
        if (!sidebar) return;
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay?.classList.add('opacity-0');
        setTimeout(() => {
            sidebarOverlay?.classList.add('hidden');
        }, 300);
        document.body.style.overflow = '';
    };

    if (sidebarToggle) sidebarToggle.addEventListener('click', openSidebar);
    if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar && !sidebar.classList.contains('-translate-x-full')) {
            closeSidebar();
        }
    });

    document.querySelectorAll('.sidebar-dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            const dropdown = toggle.closest('.sidebar-dropdown');
            const content = dropdown.querySelector('.sidebar-dropdown-content');
            const chevron = toggle.querySelector('.fa-chevron-down');

            const isHidden = content.classList.contains('hidden');
            
            if (isHidden) {
                document.querySelectorAll('.sidebar-dropdown-content').forEach(c => c.classList.add('hidden'));
                document.querySelectorAll('.sidebar-dropdown-toggle .fa-chevron-down').forEach(c => c.classList.remove('rotate-180'));

                content.classList.remove('hidden');
                chevron.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                chevron.classList.remove('rotate-180');
            }
        });
    });

    document.querySelectorAll('#sidebar nav a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) closeSidebar();
        });
    });
});
