window.openEdit = function(data, type) {
    if (type === 'sparepart') {
        const form = document.getElementById('editForm');
        if (!form) return;
        
        form.action = `/admin/spareparts/${data.id}`;
        document.getElementById('edit_name').value  = data.name;
        
        const typeSelect = document.getElementById('edit_type');
        if (typeSelect) {
            typeSelect.value = data.type;
            const wrapper = typeSelect.previousElementSibling;
            if (wrapper && wrapper.hasAttribute('data-cs-init')) {
                const labelEl = wrapper.querySelector('.cs-label');
                if (labelEl) labelEl.textContent = data.type;
            }
        }
        
        document.getElementById('edit_brand').value = data.brand;
        document.getElementById('edit_stock').value = data.stock;
        document.getElementById('edit_purchase_price').value = data.purchase_price;
        document.getElementById('edit_purchase_price').dispatchEvent(new Event('input'));
        document.getElementById('edit_price').value = data.price;
        document.getElementById('edit_price').dispatchEvent(new Event('input'));
        
        const distSelect = document.getElementById('edit_distributor_id');
        if (distSelect) {
            distSelect.value = data.distributor_id;
            const wrapper = distSelect.previousElementSibling;
            if (wrapper && wrapper.hasAttribute('data-cs-init')) {
                const labelEl = wrapper.querySelector('.cs-label');
                if (labelEl) {
                    const matchedOpt = Array.from(distSelect.options).find(o => String(o.value) === String(data.distributor_id));
                    labelEl.textContent = matchedOpt ? matchedOpt.textContent.trim() : distSelect.dataset.placeholder || 'Select...';
                }
            }
        }
        
    } else if (type === 'service') {
        const form = document.getElementById('editForm');
        if (!form) return;
        
        form.action = `/admin/services/${data.id}`;
        document.getElementById('edit_service_name').value   = data.service_name;
        document.getElementById('edit_description').value    = data.description ?? '';
        document.getElementById('edit_price').value          = data.price;
        document.getElementById('edit_price').dispatchEvent(new Event('input'));
        document.getElementById('edit_estimated_time').value = data.estimated_time;
    }
    
    if (window.openModal) window.openModal('editModal');
};

window.openAddStock = function(data) {
    const form = document.getElementById('addStockForm');
    if (!form) return;
    
    form.action = `/admin/spareparts/${data.id}/purchase`;
    document.getElementById('stock_item_name').value = data.name;
    document.getElementById('stock_purchase_price').value = data.purchase_price;
    document.getElementById('stock_purchase_price').dispatchEvent(new Event('input'));
    
    const distSelect = document.getElementById('stock_distributor_id');
    if (distSelect) {
        distSelect.value = data.distributor_id;
        const wrapper = distSelect.previousElementSibling;
        if (wrapper && wrapper.hasAttribute('data-cs-init')) {
            const labelEl = wrapper.querySelector('.cs-label');
            if (labelEl) {
                const matchedOpt = Array.from(distSelect.options).find(o => String(o.value) === String(data.distributor_id));
                labelEl.textContent = matchedOpt ? matchedOpt.textContent.trim() : distSelect.dataset.placeholder || 'Select...';
            }
        }
    }
    
    if (window.openModal) window.openModal('addStockModal');
};
