document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('transactionForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const mechanicSelect = form.querySelector('select[name="mekanik_id"]');
            if (mechanicSelect && !mechanicSelect.value) {
                e.preventDefault();
                Notify.warning('Please select a mechanic before creating the transaction.', 'Mechanic Required');
            }
        });
    }
});
