const TablePagination = {
    init() {
        document.querySelectorAll('[data-table-pagination]').forEach(container => {
            this.attachBottomControls(container);
        });
    },

    attachBottomControls(container) {
        const footer = container.querySelector('[data-pagination-footer]');
        if (!footer) return;

        const perPageSelect = footer.querySelector('select[name="per_page"]');
        if (perPageSelect && !perPageSelect.dataset.bound) {
            perPageSelect.dataset.bound = '1';
            perPageSelect.addEventListener('change', () => {
                const form = perPageSelect.closest('form') || perPageSelect.form;
                if (form) form.submit();
            });
        }
    },
};

window.TablePagination = TablePagination;
document.addEventListener('DOMContentLoaded', () => TablePagination.init());
