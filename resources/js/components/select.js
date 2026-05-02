/**
 * CustomSelect — Reusable custom dropdown replacing native <select>.
 *
 * Usage in HTML:
 *   <div data-custom-select
 *        data-name="status"
 *        data-placeholder="All Status"
 *        data-value="{{ request('status') }}"
 *        data-size="sm|md"          (optional, default md)
 *        data-options='@json([...])' >  ← [{ value, label }]
 *   </div>
 *
 * Or wrapping an existing <select>:
 *   CustomSelect.replace(selectElement)
 *
 * Emits a custom event 'custom-select:change' on the wrapper with { name, value }.
 */

const CustomSelect = {
    init() {
        document.querySelectorAll('[data-custom-select]').forEach(el => {
            if (el.dataset.csInit) return;
            this.buildFromDataset(el);
        });

        document.querySelectorAll('select.cs-replace').forEach(sel => {
            if (sel.dataset.csInit) return;
            this.replace(sel);
        });
        
        if (!window._csGlobalInited) {
            window.addEventListener('scroll', () => this.closeAll(), { passive: true });
            window.addEventListener('resize', () => this.closeAll(), { passive: true });
            
            document.addEventListener('custom-select:refresh', (e) => {
                if (e.target && e.target.hasAttribute('data-custom-select')) {
                    this.buildFromDataset(e.target);
                }
            });

            window._csGlobalInited = true;
        }
    },

    buildFromDataset(container) {
        const options     = JSON.parse(container.dataset.options   || '[]');
        const name        = container.dataset.name        || '';
        const placeholder = container.dataset.placeholder || 'Select...';
        const current     = container.dataset.value       || '';
        const size        = container.dataset.size        || 'md';

        this._build(container, options, name, placeholder, current, size, null);
    },

    replace(select) {
        if (select.dataset.csInit) return;
        select.dataset.csInit = '1';

        const options     = Array.from(select.options).map(o => ({ value: o.value, label: o.textContent.trim() }));
        const name        = select.name;
        const placeholder = options[0]?.value === '' ? options[0].label : 'Select...';
        const current     = select.value;
        const size        = select.dataset.size || 'md';

        const allSelectClasses = select.className.replace('cs-replace', '').split(' ').filter(c => c.trim() !== '');
        const layoutKeys       = ['w-', 'm', 'flex', 'grid', 'col', 'row', 'z-', 'relative', 'absolute', 'sticky', 'block', 'hidden', 'inline'];
        
        const layoutClasses = allSelectClasses.filter(c => layoutKeys.some(k => c.startsWith(k)));
        const visualClasses = allSelectClasses.filter(c => !layoutClasses.includes(c));

        const wrapper = document.createElement('div');
        wrapper.className = `relative ${layoutClasses.join(' ')}`.trim();
        
        for (const key in select.dataset) {
            wrapper.dataset[key] = select.dataset[key];
        }
        select.parentNode.insertBefore(wrapper, select);
        select.style.display = 'none';

        const filteredOptions = options.filter(o => o.value && String(o.value).trim() !== '');

        this._build(wrapper, filteredOptions, name, placeholder, current, size, select, visualClasses.join(' '));
    },

    _build(container, options, name, placeholder, currentValue, size, nativeSelect, customBtnClasses = '') {
        container.dataset.csInit = '1';

        const isSm    = size === 'sm';
        const btnH    = isSm ? 'px-2 py-1 text-xs' : 'px-3 py-2 text-sm';
        const itemCls = isSm ? 'px-3 py-1.5 text-xs' : 'px-4 py-2 text-sm';

        const matchedOption = options.find(o => String(o.value) === String(currentValue));
        let currentLabel = matchedOption ? matchedOption.label : placeholder;

        const hidePlaceholder = container.dataset.hidePlaceholder === 'true' || container.getAttribute('data-hide-placeholder') === 'true';
        if (matchedOption === undefined && currentValue && hidePlaceholder) {
            currentLabel = currentValue;
        }

        const hidden = document.createElement('input');
        hidden.type  = 'hidden';
        hidden.name  = name;
        hidden.value = currentValue;

        const btn = document.createElement('button');
        btn.type  = 'button';
        
        if (customBtnClasses) {
            btn.className = `${customBtnClasses} flex items-center gap-2 transition-all w-full justify-between whitespace-nowrap overflow-hidden`.trim();
        } else {
            btn.className = `${btnH} border border-slate-200 rounded-lg bg-white text-slate-700 flex items-center gap-2 hover:border-slate-400 transition-all w-full justify-between whitespace-nowrap`;
        }
        
        btn.innerHTML = `<span class="cs-label truncate text-left">${currentLabel}</span><i class="fas fa-chevron-down text-slate-400 cs-chevron transition-transform" style="font-size:9px;flex-shrink:0"></i>`;

        const list = document.createElement('div');
        list.className = 'hidden absolute z-30 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg min-w-full py-[10px]';

        const scrollArea = document.createElement('div');
        scrollArea.className = 'overflow-y-auto max-h-64';
        list.appendChild(scrollArea);

        const isSearchable = container.dataset.search === 'true' || container.getAttribute('data-search') === 'true';
        let searchInput = null;
        if (isSearchable) {
            const searchWrapper = document.createElement('div');
            searchWrapper.className = 'px-2 pb-2 pt-2 border-b border-slate-100 sticky top-0 bg-white z-10';
            searchWrapper.innerHTML = `
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" class="cs-search-input w-full pl-9 pr-3 py-2.5 text-sm border border-slate-200 rounded-lg outline-none focus:border-slate-900 transition-colors" placeholder="Search..." autocomplete="off">
                </div>
            `;
            searchInput = searchWrapper.querySelector('input');
            let activeIndex = -1;
            const getVisibleItems = () => Array.from(scrollArea.querySelectorAll('[data-value]:not([style*="display: none"])'));

            const updateHighlight = () => {
                const items = getVisibleItems();
                scrollArea.querySelectorAll('[data-value]').forEach(el => el.classList.remove('bg-slate-50'));
                if (activeIndex >= 0 && activeIndex < items.length) {
                    const active = items[activeIndex];
                    active.classList.add('bg-slate-50');
                    active.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            };

            searchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase();
                scrollArea.querySelectorAll('[data-value]').forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(term) ? '' : 'none';
                });
                if (placeholderItem) {
                    placeholderItem.style.display = term === '' ? '' : 'none';
                }
                activeIndex = -1;
                updateHighlight();
            });
            searchInput.addEventListener('click', e => e.stopPropagation());
            searchInput.addEventListener('keydown', e => {
                const items = getVisibleItems();
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIndex = (activeIndex + 1) % items.length;
                    updateHighlight();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIndex = (activeIndex - 1 + items.length) % items.length;
                    updateHighlight();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    const target = activeIndex >= 0 ? items[activeIndex] : items[0];
                    if (target) target.click();
                }
            });
            scrollArea.appendChild(searchWrapper);
        }

        let placeholderItem = null;
        if (!hidePlaceholder) {
            placeholderItem = document.createElement('div');
            placeholderItem.className = `${itemCls} cursor-pointer text-slate-400 hover:bg-slate-50 transition-colors`;
            placeholderItem.textContent = placeholder;
            placeholderItem.addEventListener('click', (e) => {
                e.stopPropagation();
                this._select(container, btn, list, hidden, nativeSelect, '', placeholder, placeholder);
            });
            scrollArea.appendChild(placeholderItem);
        }

        options.forEach(opt => {
            const item = document.createElement('div');
            const isDisabled = opt.disabled === true;
            
            item.className = `${itemCls} flex items-center justify-between transition-colors ${isDisabled ? 'text-slate-300 cursor-not-allowed bg-slate-50/50' : 'cursor-pointer text-slate-700 hover:bg-slate-50'}`;
            item.dataset.value = opt.value;

            const isSelected = String(opt.value) === String(currentValue);
            item.innerHTML = `<span class="truncate pr-4">${opt.label}</span>${isSelected ? '<i class="fas fa-check text-slate-900 text-[10px] flex-shrink-0"></i>' : ''}`;

            if (!isDisabled) {
                item.addEventListener('click', () => {
                    this._select(container, btn, list, hidden, nativeSelect, opt.value, opt.label, placeholder);
                });
            }
            scrollArea.appendChild(item);
        });

        container.style.position = 'relative';
        
        const oldInput = container.querySelector('input[type="hidden"]');
        const oldBtn   = container.querySelector('button');
        const oldList  = container.querySelector('div[class*="absolute"]');
        
        if (oldInput) oldInput.remove();
        if (oldBtn) oldBtn.remove();
        if (oldList) oldList.remove();
        
        const containerId = container.dataset.csId || Math.random().toString(36).substr(2, 9);
        container.dataset.csId = containerId;
        list.dataset.csContainerId = containerId;

        container.appendChild(hidden);
        container.appendChild(btn);
        container.appendChild(list);

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            const isOpen = !list.classList.contains('hidden');
            
            if (window.closeAllDropdowns) {
                window.closeAllDropdowns();
            } else {
                this.closeAll();
            }

            if (!isOpen) {
                const rect = btn.getBoundingClientRect();
                const vh = window.innerHeight;
                const spaceBelow = vh - rect.bottom;
                const spaceAbove = rect.top;
                const listHeight = 300;

                document.body.appendChild(list);
                list.classList.remove('hidden');
                list.style.width = `${rect.width}px`;
                list.style.left = `${rect.left + window.scrollX}px`;
                list.style.zIndex = '100';
                list.style.minWidth = size === 'sm' ? `${rect.width}px` : '160px';
                list.classList.remove('min-w-full');

                if (spaceBelow < listHeight && spaceAbove > spaceBelow) {
                    list.style.top = 'auto';
                    list.style.bottom = `${vh - rect.top - window.scrollY}px`;
                    list.classList.remove('mt-1');
                    list.classList.add('mb-1');
                } else {
                    list.style.top = `${rect.bottom + window.scrollY}px`;
                    list.style.bottom = 'auto';
                    list.classList.add('mt-1');
                    list.classList.remove('mb-1');
                }

                btn.querySelector('.cs-chevron')?.classList.add('rotate-180');

                if (searchInput) {
                    searchInput.value = '';
                    list.querySelectorAll('[data-value]').forEach(item => item.style.display = '');
                    if (placeholderItem) placeholderItem.style.display = '';
                    setTimeout(() => searchInput.focus(), 50);
                }
            }
        });


        document.addEventListener('click', (e) => {
            const isClickInside = container.contains(e.target) || list.contains(e.target);
            if (!isClickInside) this.closeAll();
        });
    },

    _select(container, btn, list, hidden, nativeSelect, value, label, placeholder) {
        container.dataset.value = value;
        hidden.value = value;
        const labelEl = btn.querySelector('.cs-label');
        if (labelEl) labelEl.textContent = value ? label : placeholder;

        if (nativeSelect) {
            nativeSelect.value = value;
            nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }

        list.querySelectorAll('[data-value]').forEach(item => {
            item.querySelector('.fa-check')?.remove();
            if (String(item.dataset.value) === String(value)) {
                const check = document.createElement('i');
                check.className = 'fas fa-check text-slate-900 text-[10px] flex-shrink-0';
                item.appendChild(check);
            }
        });

        this.closeAll();

        container.dispatchEvent(new CustomEvent('custom-select:change', {
            bubbles: true, detail: { name: hidden.name, value }
        }));
    },

    closeAll() {
        document.querySelectorAll('body > div[class*="absolute"]').forEach(list => {
            const containerId = list.dataset.csContainerId;
            if (containerId) {
                const container = document.querySelector(`[data-cs-id="${containerId}"]`);
                if (container) {
                    container.appendChild(list);
                    list.classList.add('hidden');
                    container.querySelector('.cs-chevron')?.classList.remove('rotate-180');
                }
            }
        });

        document.querySelectorAll('[data-cs-init="1"]').forEach(container => {
            const list = container.querySelector('div[class*="absolute"]');
            const chevron = container.querySelector('.cs-chevron');
            if (list) list.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        });
    },
};

window.CustomSelect = CustomSelect;
document.addEventListener('DOMContentLoaded', () => CustomSelect.init());
