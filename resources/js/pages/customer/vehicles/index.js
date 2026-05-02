document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[id$="Modal"]').forEach(m => {
        m.addEventListener('click', e => {
            if (e.target === m) {
                if (window.closeModal) window.closeModal(m.id);
                else m.classList.add('hidden');
            }
        });
    });
});

window.openEdit = function(v) {
    const form = document.getElementById('editForm');
    if (!form) return;

    form.action = `/customer/vehicles/${v.id}`;
    document.getElementById('e_brand').value = v.brand;
    document.getElementById('e_model').value = v.model;
    document.getElementById('e_year').value  = v.year;
    document.getElementById('e_color').value = v.color;
    document.getElementById('e_plate').value = v.license_plate;
    
    if (window.openModal) window.openModal('editModal');
    else document.getElementById('editModal').classList.remove('hidden');
};
