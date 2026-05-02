document.addEventListener('DOMContentLoaded', () => {
    const timeWrapper = document.querySelector('[data-name="booking_time"]')?.closest('[data-custom-select]');
    if (!timeWrapper) return;

    const currentDay = timeWrapper.dataset.currentDay;
    const currentHour = timeWrapper.dataset.currentHour;
    const baseOptions = JSON.parse(timeWrapper.dataset.baseOptions || '[]');

    function updateTimeOptions(selectedDate) {
        let newOptions;
        if (selectedDate === currentDay) {
            newOptions = baseOptions.map(opt => ({
                ...opt,
                disabled: opt.value < currentHour
            }));
        } else {
            newOptions = baseOptions.map(opt => ({ ...opt, disabled: false }));
        }

        timeWrapper.dataset.options = JSON.stringify(newOptions);
        
        const currentVal = timeWrapper.querySelector('input[type="hidden"]').value;
        const isNowDisabled = newOptions.find(o => o.value === currentVal)?.disabled;
        
        if (isNowDisabled) {
            timeWrapper.dataset.value = "";
            timeWrapper.querySelector('input[type="hidden"]').value = "";
            const labelEl = timeWrapper.querySelector('.cs-label');
            if (labelEl) labelEl.textContent = timeWrapper.dataset.placeholder || "Select time...";
        }

        if (window.CustomSelect) {
            window.CustomSelect.buildFromDataset(timeWrapper);
        }
    }

    document.addEventListener('datepicker:change', (e) => {
        if (e.detail.name === 'booking_date') {
            updateTimeOptions(e.detail.value);
        }
    });

    const dateInput = document.querySelector('[data-name="booking_date"] input[type="hidden"]');
    if (dateInput && dateInput.value === currentDay) {
        updateTimeOptions(dateInput.value);
    }

    const searchInput = document.getElementById('serviceSearch');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.service-item');
            const noFound = document.getElementById('noServiceFound');
            let visibleCount = 0;

            items.forEach(item => {
                const name = item.querySelector('.service-name').textContent.toLowerCase();
                if (name.includes(query)) {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (noFound) {
                noFound.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });
    }
});
