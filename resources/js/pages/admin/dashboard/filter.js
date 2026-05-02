document.addEventListener('DOMContentLoaded', () => {
    const searchInput  = document.getElementById('dashboardSearch');
    const statusFilter = document.getElementById('dashboardStatus');
    const tableBody    = document.getElementById('recentBookingsBody');

    if (!tableBody) return;

    function applyFilter() {
        const term   = (searchInput?.value  || '').toLowerCase().trim();
        const statusEl = statusFilter?.querySelector('input[type="hidden"]');
        const status   = (statusEl ? statusEl.value : (statusFilter?.value || '')).toLowerCase().trim();
        
        const rows   = tableBody.querySelectorAll('tr[data-customer]');
        let   shown  = 0;

        rows.forEach(row => {
            const customer  = (row.dataset.customer  || '').toLowerCase();
            const rowStatus = (row.dataset.status    || '').toLowerCase();
            const matchTerm   = !term   || customer.includes(term);
            const matchStatus = !status || rowStatus === status;

            if (matchTerm && matchStatus) {
                row.classList.remove('hidden');
                shown++;
            } else {
                row.classList.add('hidden');
            }
        });

        const emptyRow = tableBody.querySelector('tr[data-empty]');
        if (emptyRow) emptyRow.classList.toggle('hidden', shown > 0);
    }

    searchInput?.addEventListener('input', applyFilter);
    statusFilter?.addEventListener('change', applyFilter);
    statusFilter?.addEventListener('custom-select:change', applyFilter);
});
