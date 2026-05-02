/**
 * AutoFilter — Submits filter forms automatically without a submit button.
 *
 * Behaviours:
 *  - Text/search inputs: debounced 500ms after user stops typing
 *  - Select dropdowns (native or custom): submit immediately on change
 *  - Date inputs / custom datepicker: submit immediately on change
 *
 * Usage:
 *   Add data-auto-filter to the <form> element.
 *   <form method="GET" data-auto-filter>...</form>
 *
 * The filter button can be removed from the HTML, or hidden via CSS.
 * A "Clear" link still works normally via href.
 */
const AutoFilter = {
    DEBOUNCE_MS: 500,

    init() {
        document.querySelectorAll('form[data-auto-filter]').forEach(form => {
            if (form.dataset.afInit) return;
            form.dataset.afInit = '1';
            this.attachForm(form);
        });

        if (!document.dataset?.afGlobalInit) {
            if (!document.body.dataset) document.body.dataset = {};
            if (document.body.dataset.afGlobalInit) return;
            document.body.dataset.afGlobalInit = '1';

            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link || !link.href) return;

                const isCurrentPage = link.href.split('?')[0] === window.location.href.split('?')[0];
                const isAjaxContainer = link.closest('#filter-container') || link.closest('#clear-container');

                if (isCurrentPage && isAjaxContainer && !link.hasAttribute('data-no-ajax')) {
                    e.preventDefault();
                    const isClear = link.closest('#clear-container') !== null;
                    this.handleFilter(null, link.href, isClear);
                }
            });
        }
    },

    attachForm(form) {
        let debounceTimer = null;

        const trigger = () => this.handleFilter(form);
        const triggerDebounced = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(trigger, this.DEBOUNCE_MS);
        };

        form.querySelectorAll('input[type="text"], input[type="search"]').forEach(input => {
            input.addEventListener('input', triggerDebounced);
        });
        form.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', trigger);
        });
        form.querySelectorAll('input[type="date"]').forEach(input => {
            input.addEventListener('change', trigger);
        });
        
        form.addEventListener('custom-select:change', trigger);
        form.addEventListener('datepicker:change', trigger);
        
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            trigger();
        });

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(btn => {
            btn.style.display = 'none';
        });
    },

    async handleFilter(form, manualUrl = null, forceReset = false) {
        const container = document.getElementById('filter-container');
        if (!container) {
            if (form) form.submit();
            return;
        }
        
        let url = manualUrl;
        if (!url && form) {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            url = `${window.location.pathname}?${params.toString()}`;
        }

        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const targetIds = ['filter-container', 'clear-container', 'tabs-container'];
            let found = false;

            targetIds.forEach(id => {
                const oldEl = document.getElementById(id);
                const newEl = doc.getElementById(id);
                if (oldEl && newEl) {
                    oldEl.innerHTML = newEl.innerHTML;
                    found = true;
                }
            });

            if (found) {
                if (forceReset || (manualUrl && !manualUrl.includes('?'))) {
                    document.querySelectorAll('form[data-auto-filter]').forEach(f => {
                        f.reset();
                        f.querySelectorAll('[data-custom-select], [data-datepicker]').forEach(c => {
                            const defaultValue = c.dataset.paginationFooter === 'true' ? '10' : '';
                            c.dataset.value = defaultValue;
                            
                            if (c.hasAttribute('data-custom-select') && window.CustomSelect) {
                                window.CustomSelect.buildFromDataset(c);
                            }
                            if (c.hasAttribute('data-datepicker') && window.CustomDatePicker) {
                                const name = c.dataset.name;
                                const val  = defaultValue;
                                const placeholder = c.dataset.placeholder || 'Select date';
                                window.CustomDatePicker.build(c, name, val, placeholder);
                            }
                        });
                    });
                }

                window.history.pushState({ path: url }, '', url);
                this.reinit();
            } else {
                window.location.href = url;
            }
        } catch (err) {
            console.error('Filter failed:', err);
            window.location.href = url;
        } finally {
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
        }
    },

    reinit() {
        setTimeout(() => {
            if (window.CustomSelect) window.CustomSelect.init();
            if (window.CustomDatePicker) window.CustomDatePicker.init();
            if (window.AutoFilter) window.AutoFilter.init();
            
            if (window.lucide) lucide.createIcons();
        }, 10);
    }
};

window.AutoFilter = AutoFilter;
window.onpopstate = () => window.location.reload();

document.addEventListener('DOMContentLoaded', () => AutoFilter.init());
