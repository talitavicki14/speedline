/**
 * CustomDatePicker — Minimal custom date picker replacing native <input type="date">.
 *
 * Usage:
 *   <div data-datepicker data-name="date" data-value="{{ request('date') }}" data-placeholder="Pick a date"></div>
 *
 * Or auto-replace:
 *   <input type="date" class="dp-replace" name="date" value="...">
 */
const CustomDatePicker = {
    MONTHS: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    DAYS: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],

    init() {
        document.querySelectorAll('[data-datepicker]').forEach(el => {
            if (el.dataset.dpInit) return;
            this.build(el, el.dataset.name, el.dataset.value || '', el.dataset.placeholder || 'Pick a date');
        });

        document.querySelectorAll('input[type="date"].dp-replace').forEach(input => {
            if (input.dataset.dpInit) return;
            this.replaceInput(input);
        });

        if (!window._dpGlobalInited) {
            window.addEventListener('scroll', () => this.closeAll(), { passive: true });
            window.addEventListener('resize', () => this.closeAll(), { passive: true });
            window._dpGlobalInited = true;
        }
    },

    replaceInput(input) {
        input.dataset.dpInit = '1';
        const wrapper = document.createElement('div');
        wrapper.className = input.className.replace('dp-replace', '').trim();
        input.parentNode.insertBefore(wrapper, input);
        input.style.display = 'none';
        this.build(wrapper, input.name, input.value, input.placeholder || 'Select date', input);
    },

    build(container, name, currentValue, placeholder, nativeInput = null) {
        container.dataset.dpInit = '1';
        container.style.position = 'relative';

        const oldInput = container.querySelector('input[type="hidden"]');
        const oldBtn   = container.querySelector('button');
        const oldPanel = container.querySelector('div[class*="absolute"]');
        
        if (oldInput) oldInput.remove();
        if (oldBtn) oldBtn.remove();
        if (oldPanel) oldPanel.remove();

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = name;
        hidden.value = currentValue;

        const displayLabel = currentValue ? this._formatDisplay(currentValue) : placeholder;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'px-3 py-2 text-sm border border-slate-200 rounded-lg bg-white text-slate-700 flex items-center gap-2 hover:border-slate-400 transition-colors w-full justify-between whitespace-nowrap';
        btn.innerHTML = `<span class="dp-label">${displayLabel}</span><i class="fas fa-calendar-alt text-slate-400" style="font-size:11px;flex-shrink:0"></i>`;

        const panel = document.createElement('div');
        panel.className = 'hidden absolute z-30 mt-1 bg-white border border-slate-200 rounded-2xl shadow-xl p-4 w-72 py-5';
        
        const containerId = container.dataset.dpId || Math.random().toString(36).substr(2, 9);
        container.dataset.dpId = containerId;
        panel.dataset.dpContainerId = containerId;

        container.appendChild(hidden);
        container.appendChild(btn);
        container.appendChild(panel);

        const now = currentValue ? new Date(currentValue + 'T00:00:00') : new Date();
        container._dpState = { 
            year: now.getFullYear(), 
            month: now.getMonth(), 
            selected: currentValue,
            viewMode: 'days'
        };

        this._renderCalendar(container, panel, btn, hidden, nativeInput);

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            const isOpen = !panel.classList.contains('hidden');

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
                const panelHeight = 350;

                document.body.appendChild(panel);
                panel.classList.remove('hidden');
                panel.style.zIndex = '70';
                panel.style.left = `${rect.left + window.scrollX}px`;
                panel.classList.remove('w-72');
                panel.style.width = '288px';

                if (spaceBelow < panelHeight && spaceAbove > spaceBelow) {
                    panel.style.top = 'auto';
                    panel.style.bottom = `${vh - rect.top - window.scrollY}px`;
                } else {
                    panel.style.top = `${rect.bottom + window.scrollY}px`;
                    panel.style.bottom = 'auto';
                }
                
                container._dpState.viewMode = 'days';
                this._renderCalendar(container, panel, btn, hidden, nativeInput);
            }
        });

        document.addEventListener('click', (e) => {
            const isInside = container.contains(e.target) || panel.contains(e.target);
            if (!isInside) this.closeAll();
        });
        panel.addEventListener('click', e => e.stopPropagation());
    },

    _getToday() {
        const serverDate = document.querySelector('meta[name="app-date"]')?.content;
        if (serverDate) return serverDate;

        const now = new Date();
        const offset = 7;
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        const jktTime = new Date(utc + (3600000 * offset));
        return jktTime.toISOString().split('T')[0];
    },

    _renderCalendar(container, panel, btn, hidden, nativeInput) {
        const { year, month, selected, viewMode } = container._dpState;
        
        if (viewMode === 'months') {
            this._renderMonths(container, panel, btn, hidden, nativeInput);
            return;
        }
        
        if (viewMode === 'years') {
            this._renderYears(container, panel, btn, hidden, nativeInput);
            return;
        }

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        panel.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <button type="button" class="dp-prev w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 transition-colors">
                    <i class="fas fa-chevron-left" style="font-size:10px"></i>
                </button>
                <button type="button" class="dp-mode-toggle text-sm font-semibold text-slate-800 hover:text-slate-500 transition-colors px-2 py-0.5 rounded-md hover:bg-slate-50">
                    ${this.MONTHS[month]} ${year}
                </button>
                <button type="button" class="dp-next w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500 transition-colors">
                    <i class="fas fa-chevron-right" style="font-size:10px"></i>
                </button>
            </div>
            <div class="grid grid-cols-7 gap-0.5 mb-1">
                ${this.DAYS.map(d => `<div class="text-center text-[10px] font-semibold text-slate-400 py-1">${d}</div>`).join('')}
            </div>
            <div class="grid grid-cols-7 gap-0.5" id="dp-days-${month}-${year}"></div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex justify-between items-center px-1">
                ${container.dataset.hideToday === 'true' ? '<div></div>' : '<button type="button" class="dp-today text-[11px] font-medium text-slate-400 hover:text-slate-800 transition-colors">Hari Ini</button>'}
                ${selected ? `<button type="button" class="dp-clear text-[11px] font-medium text-slate-400 hover:text-red-500 transition-colors">Hapus</button>` : ''}
            </div>
        `;

        const grid = panel.querySelector(`#dp-days-${month}-${year}`);
        const minDate = container.dataset.min || null;
        const maxDate = container.dataset.max || null;
        const today = this._getToday();

        for (let i = 0; i < firstDay; i++) {
            grid.insertAdjacentHTML('beforeend', '<div></div>');
        }
        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const isSel = dateStr === selected;
            const isToday = dateStr === today;
            
            let isDisabled = false;
            if (minDate && dateStr < minDate) isDisabled = true;
            if (maxDate && dateStr > maxDate) isDisabled = true;

            const dayBtn = document.createElement('button');
            dayBtn.type = 'button';
            dayBtn.className = `w-full aspect-square rounded-lg text-xs transition-colors flex items-center justify-center
                ${isSel ? 'bg-slate-900 text-white font-bold' : (isToday ? 'bg-slate-100 text-slate-900 border border-slate-300 font-bold' : 'text-slate-700 hover:bg-slate-100')}
                ${isDisabled ? 'opacity-20 cursor-not-allowed' : ''}`;
            
            if (isDisabled) dayBtn.disabled = true;
            dayBtn.textContent = d;
            if (!isDisabled) {
                dayBtn.addEventListener('click', () => this._pick(container, panel, btn, hidden, nativeInput, dateStr));
            }
            grid.appendChild(dayBtn);
        }

        panel.querySelector('.dp-prev')?.addEventListener('click', () => {
            const s = container._dpState;
            if (s.month === 0) { s.month = 11; s.year--; } else { s.month--; }
            this._renderCalendar(container, panel, btn, hidden, nativeInput);
        });
        panel.querySelector('.dp-next')?.addEventListener('click', () => {
            const s = container._dpState;
            if (s.month === 11) { s.month = 0; s.year++; } else { s.month++; }
            this._renderCalendar(container, panel, btn, hidden, nativeInput);
        });
        panel.querySelector('.dp-mode-toggle')?.addEventListener('click', () => {
            container._dpState.viewMode = 'months';
            this._renderCalendar(container, panel, btn, hidden, nativeInput);
        });
        panel.querySelector('.dp-today')?.addEventListener('click', () => {
            this._pick(container, panel, btn, hidden, nativeInput, today);
        });
        panel.querySelector('.dp-clear')?.addEventListener('click', () => {
            this._pick(container, panel, btn, hidden, nativeInput, '');
        });
    },

    _renderMonths(container, panel, btn, hidden, nativeInput) {
        const { year } = container._dpState;
        panel.innerHTML = `
            <div class="flex items-center justify-between mb-4 px-1">
                <button type="button" class="dp-year-toggle text-sm font-bold text-slate-800 hover:bg-slate-50 px-3 py-1 rounded-lg transition-all">${year}</button>
                <div class="flex gap-1">
                    <button type="button" class="dp-prev-year w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500"><i class="fas fa-chevron-left text-[10px]"></i></button>
                    <button type="button" class="dp-next-year w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500"><i class="fas fa-chevron-right text-[10px]"></i></button>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                ${this.MONTHS.map((m, i) => `
                    <button type="button" class="dp-month-select py-3 text-xs rounded-xl transition-all border border-transparent 
                        ${container._dpState.month === i ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-50 hover:border-slate-100'}" 
                        data-month="${i}">${m.substr(0, 3)}</button>
                `).join('')}
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <button type="button" class="dp-back-to-days w-full py-2 text-[10px] uppercase tracking-widest font-bold text-slate-400 hover:text-slate-700">Batal</button>
            </div>
        `;

        panel.querySelectorAll('.dp-month-select').forEach(b => {
            b.addEventListener('click', () => {
                container._dpState.month = parseInt(b.dataset.month);
                container._dpState.viewMode = 'days';
                this._renderCalendar(container, panel, btn, hidden, nativeInput);
            });
        });
        panel.querySelector('.dp-year-toggle').addEventListener('click', () => {
            container._dpState.viewMode = 'years';
            this._renderCalendar(container, panel, btn, hidden, nativeInput);
        });
        panel.querySelector('.dp-prev-year').addEventListener('click', () => {
            container._dpState.year--;
            this._renderMonths(container, panel, btn, hidden, nativeInput);
        });
        panel.querySelector('.dp-next-year').addEventListener('click', () => {
            container._dpState.year++;
            this._renderMonths(container, panel, btn, hidden, nativeInput);
        });
        panel.querySelector('.dp-back-to-days').addEventListener('click', () => {
            container._dpState.viewMode = 'days';
            this._renderCalendar(container, panel, btn, hidden, nativeInput);
        });
    },

    _renderYears(container, panel, btn, hidden, nativeInput) {
        const { year } = container._dpState;
        const startYear = year - 4;
        
        panel.innerHTML = `
            <div class="flex items-center justify-between mb-4 px-1">
                <span class="text-sm font-bold text-slate-800">Pilih Tahun</span>
                <div class="flex gap-1">
                    <button type="button" class="dp-prev-years-page w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500"><i class="fas fa-chevron-left text-[10px]"></i></button>
                    <button type="button" class="dp-next-years-page w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-500"><i class="fas fa-chevron-right text-[10px]"></i></button>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                ${Array.from({length: 12}, (_, i) => startYear + i).map(y => `
                    <button type="button" class="dp-year-select py-3 text-xs rounded-xl transition-all border border-transparent
                        ${container._dpState.year === y ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-50 hover:border-slate-100'}" 
                        data-year="${y}">${y}</button>
                `).join('')}
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <button type="button" class="dp-back-to-months w-full py-2 text-[10px] uppercase tracking-widest font-bold text-slate-400 hover:text-slate-700">Kembali</button>
            </div>
        `;

        panel.querySelectorAll('.dp-year-select').forEach(b => {
            b.addEventListener('click', () => {
                container._dpState.year = parseInt(b.dataset.year);
                container._dpState.viewMode = 'months';
                this._renderCalendar(container, panel, btn, hidden, nativeInput);
            });
        });
        panel.querySelector('.dp-prev-years-page').addEventListener('click', () => {
            container._dpState.year -= 12;
            this._renderYears(container, panel, btn, hidden, nativeInput);
        });
        panel.querySelector('.dp-next-years-page').addEventListener('click', () => {
            container._dpState.year += 12;
            this._renderYears(container, panel, btn, hidden, nativeInput);
        });
        panel.querySelector('.dp-back-to-months').addEventListener('click', () => {
            container._dpState.viewMode = 'months';
            this._renderCalendar(container, panel, btn, hidden, nativeInput);
        });
    },

    _pick(container, panel, btn, hidden, nativeInput, dateStr) {
        container.dataset.value = dateStr;
        if (dateStr) {
            const minDate = container.dataset.min;
            const maxDate = container.dataset.max;
            if (minDate && dateStr < minDate) return;
            if (maxDate && dateStr > maxDate) return;
            
            const [y, m, d] = dateStr.split('-');
            container._dpState.year = parseInt(y);
            container._dpState.month = parseInt(m) - 1;
        }
        container._dpState.selected = dateStr;
        hidden.value = dateStr;
        const labelEl = btn.querySelector('.dp-label');
        if (labelEl) labelEl.textContent = dateStr ? this._formatDisplay(dateStr) : btn.dataset.placeholder || 'Select date';
        if (nativeInput) {
            nativeInput.value = dateStr;
            nativeInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        this.closeAll();
        this._renderCalendar(container, panel, btn, hidden, nativeInput);
        container.dispatchEvent(new CustomEvent('datepicker:change', {
            bubbles: true, detail: { name: hidden.name, value: dateStr }
        }));
    },

    _formatDisplay(dateStr) {
        if (!dateStr) return '';
        const [y, m, d] = dateStr.split('-');
        return `${parseInt(d)} ${this.MONTHS[parseInt(m) - 1]} ${y}`;
    },

    closeAll() {
        document.querySelectorAll('body > div[class*="absolute"]').forEach(p => {
            const cid = p.dataset.dpContainerId;
            if (cid) {
                const container = document.querySelector(`[data-dp-id="${cid}"]`);
                if (container) {
                    container.appendChild(p);
                    p.classList.add('hidden');
                }
            }
        });

        document.querySelectorAll('[data-dp-init] div[class*="absolute"]').forEach(p => p.classList.add('hidden'));
    },
};

window.CustomDatePicker = CustomDatePicker;
document.addEventListener('DOMContentLoaded', () => CustomDatePicker.init());