document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('txForm');
    if (!form) return;

    const rawSpareparts = form.dataset.spareparts;
    const spareparts    = rawSpareparts ? JSON.parse(rawSpareparts) : [];
    const serviceTotal  = parseFloat(form.dataset.serviceTotal || 0);
    
    let rowIndex        = 0;
    let sparepartTotals = {};

    const formatRp = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');

    const updateSummary = () => {
        let partsTotal = Object.values(sparepartTotals).reduce((a, b) => a + b, 0);
        document.getElementById('partsTotal').textContent = formatRp(partsTotal);
        document.getElementById('grandTotal').textContent = formatRp(serviceTotal + partsTotal);

        const summaryContainer = document.getElementById('sparepartSummaryRows');
        const emptyNote        = document.getElementById('sparepartEmptyNote');
        summaryContainer.querySelectorAll('.sp-summary-row').forEach(r => r.remove());

        const hasItems = Object.keys(sparepartTotals).some(idx => sparepartTotals[idx] > 0);
        emptyNote.style.display = hasItems ? 'none' : '';

        Object.keys(sparepartTotals).forEach(idx => {
            const selectEl = document.querySelector(`select[data-idx="${idx}"]`);
            if (!selectEl || !selectEl.value) return;
            const label    = selectEl.options[selectEl.selectedIndex]?.text?.split(' — ')[0] ?? '—';
            const qtyEl    = document.getElementById('sp-qty-' + idx);
            const qty      = parseInt(qtyEl?.value) || 0;
            const subtotal = sparepartTotals[idx];
            if (qty === 0 || subtotal === 0) return;

            const row = document.createElement('div');
            row.className = 'sp-summary-row flex justify-between items-center px-5 py-3';
            row.innerHTML = `
                <div><span class="text-sm text-slate-700">${label}</span><span class="text-xs text-slate-400 ml-1">×${qty}</span></div>
                <span class="font-semibold text-sm text-slate-900">${formatRp(subtotal)}</span>
            `;
            summaryContainer.appendChild(row);
        });
    };

    const onSparepartChange = (idx) => {
        const selectEl = document.querySelector(`select[data-idx="${idx}"]`);
        const qtyEl    = document.getElementById('sp-qty-' + idx);
        if (!selectEl || !qtyEl) return;

        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const price          = parseFloat(selectedOption?.dataset?.price || 0);
        const qty            = parseInt(qtyEl.value) || 0;
        sparepartTotals[idx] = price * qty;
        updateSummary();
    };

    const checkEmpty = () => {
        const rows = document.getElementById('sparepart-rows').children;
        document.getElementById('no-spareparts').style.display = rows.length === 0 ? '' : 'none';
    };

    window.addSparepart = () => {
        const noPartsLabel = document.getElementById('no-spareparts');
        if (noPartsLabel) noPartsLabel.style.display = 'none';
        
        const idx = rowIndex++;
        const row = document.createElement('div');
        row.className = 'flex gap-3 items-center';
        row.id = 'sp-row-' + idx;
        row.innerHTML = `
            <select name="spareparts[${idx}][sparepart_id]" required
                    data-idx="${idx}"
                    class="flex-1 border border-slate-200 focus:border-slate-900 rounded-xl px-3 py-2.5 text-sm outline-none transition-colors bg-white text-slate-700 sp-select-live">
                <option value="">Select part...</option>
                ${spareparts.map(s => `<option value="${s.id}" data-price="${s.price}">${s.name} — ${s.brand} (Stock: ${s.stock})</option>`).join('')}
            </select>
            <input type="number" name="spareparts[${idx}][qty]" min="1" value="1"
                   id="sp-qty-${idx}"
                   placeholder="Qty" required
                   class="w-20 border border-slate-200 focus:border-slate-900 rounded-xl px-3 py-2.5 text-sm outline-none transition-colors text-center text-slate-700 sp-qty-live">
            <button type="button" onclick="removeSparepart(${idx})"
                    class="text-slate-300 hover:text-red-400 transition-colors p-1">
                <i class="fas fa-trash text-xs"></i>
            </button>
        `;
        document.getElementById('sparepart-rows').appendChild(row);
        
        const select = row.querySelector('.sp-select-live');
        const qty    = row.querySelector('.sp-qty-live');
        
        const handleEvent = () => onSparepartChange(idx);
        select.addEventListener('change', handleEvent);
        qty.addEventListener('change', handleEvent);
        qty.addEventListener('input', handleEvent);

        sparepartTotals[idx] = 0;
    };

    window.removeSparepart = (idx) => {
        const row = document.getElementById('sp-row-' + idx);
        if (row) row.remove();
        delete sparepartTotals[idx];
        checkEmpty();
        updateSummary();
    };
});
