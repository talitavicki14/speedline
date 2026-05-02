document.addEventListener('DOMContentLoaded', () => {
    ['addServiceModal', 'addSparepartModal'].forEach(id => {
        const modal = document.getElementById(id);
        if (modal) {
            modal.addEventListener('click', e => {
                if (e.target === modal) closeModal(id);
            });
        }
    });
});

window.openAddService   = () => openModal('addServiceModal');
window.openAddSparepart = () => openModal('addSparepartModal');
