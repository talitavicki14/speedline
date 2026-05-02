document.addEventListener('DOMContentLoaded', function() {
    let cart = [];
    const sparepartList = document.getElementById('sparepartList');
    const sparepartSearch = document.getElementById('sparepartSearch');
    const cartItemsContainer = document.getElementById('cartItems');
    const totalQtyEl = document.getElementById('totalQty');
    const totalAmountEl = document.getElementById('totalAmount');
    const amountPaidEl = document.getElementById('amountPaid');
    const changeAmountEl = document.getElementById('changeAmount');
    const btnCheckout = document.getElementById('btnCheckout');
    const clearCartBtn = document.getElementById('clearCart');
    const cartItemTemplate = document.getElementById('cartItemTemplate');

    // --- Search Logic ---
    if (sparepartSearch) {
        sparepartSearch.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const items = sparepartList.querySelectorAll('.sparepart-item');
            
            items.forEach(item => {
                const name = item.dataset.name.toLowerCase();
                const brand = item.dataset.brand.toLowerCase();
                const type = item.dataset.type.toLowerCase();
                if (name.includes(query) || brand.includes(query) || type.includes(query)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        });
    }

    // --- Cart Logic ---
    function updateCartUI() {
        // Clear current UI (except template)
        cartItemsContainer.innerHTML = '';

        if (cart.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="empty-cart flex flex-col items-center justify-center h-full text-center py-10">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-shopping-basket text-slate-200 text-2xl"></i>
                    </div>
                    <p class="text-xs text-slate-400 font-medium">Keranjang masih kosong</p>
                </div>
            `;
            totalQtyEl.textContent = '0';
            totalAmountEl.textContent = 'Rp 0';
            btnCheckout.disabled = true;
            updateChange();
            return;
        }

        let totalAmount = 0;
        let totalQty = 0;

        cart.forEach((item, index) => {
            const clone = cartItemTemplate.content.cloneNode(true);
            const subtotal = item.price * item.qty;
            
            totalAmount += subtotal;
            totalQty += item.qty;

            clone.querySelector('.item-name').textContent = item.name;
            clone.querySelector('.item-price').textContent = `Rp ${formatNumber(item.price)} x ${item.qty}`;
            clone.querySelector('.item-qty').textContent = item.qty;
            clone.querySelector('.item-subtotal').textContent = `Rp ${formatNumber(subtotal)}`;

            // Events
            clone.querySelector('.btn-qty-minus').addEventListener('click', () => updateQty(index, -1));
            clone.querySelector('.btn-qty-plus').addEventListener('click', () => updateQty(index, 1));
            clone.querySelector('.btn-remove').addEventListener('click', () => removeItem(index));

            cartItemsContainer.appendChild(clone);
        });

        totalQtyEl.textContent = totalQty;
        totalAmountEl.textContent = `Rp ${formatNumber(totalAmount)}`;
        btnCheckout.disabled = false;
        updateChange();
    }

    function addToCart(sparepart) {
        const existingIndex = cart.findIndex(item => item.id === sparepart.id);
        
        if (existingIndex > -1) {
            if (cart[existingIndex].qty + 1 > sparepart.stock) {
                Notify.warning('Stok tidak mencukupi.', 'Peringatan');
                return;
            }
            cart[existingIndex].qty += 1;
        } else {
            cart.push({
                id: sparepart.id,
                name: sparepart.name,
                price: parseFloat(sparepart.price),
                qty: 1,
                stock: parseInt(sparepart.stock)
            });
        }
        updateCartUI();
    }

    function updateQty(index, delta) {
        const item = cart[index];
        const newQty = item.qty + delta;

        if (newQty < 1) {
            removeItem(index);
            return;
        }

        if (newQty > item.stock) {
            Notify.warning('Stok tidak mencukupi.', 'Peringatan');
            return;
        }

        item.qty = newQty;
        updateCartUI();
    }

    function removeItem(index) {
        cart.splice(index, 1);
        updateCartUI();
    }

    function updateChange() {
        const total = getCartTotal();
        const paidStr = amountPaidEl.value.replace(/\D/g, '');
        const paid = paidStr ? parseFloat(paidStr) : 0;
        const change = paid - total;

        if (change >= 0) {
            changeAmountEl.textContent = `Rp ${formatNumber(change)}`;
            changeAmountEl.classList.remove('text-red-500');
            changeAmountEl.classList.add('text-emerald-600');
        } else {
            changeAmountEl.textContent = `- Rp ${formatNumber(Math.abs(change))}`;
            changeAmountEl.classList.remove('text-emerald-600');
            changeAmountEl.classList.add('text-red-500');
        }
    }

    function getCartTotal() {
        return cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    // --- Events ---
    if (sparepartList) {
        sparepartList.addEventListener('click', function(e) {
            const item = e.target.closest('.sparepart-item');
            if (item) {
                addToCart({
                    id: item.dataset.id,
                    name: item.dataset.name,
                    price: item.dataset.price,
                    stock: item.dataset.stock
                });
            }
        });
    }

    if (amountPaidEl) {
        amountPaidEl.addEventListener('input', updateChange);
    }

    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', () => {
            if (cart.length > 0) {
                Notify.confirm('Kosongkan keranjang belanja?', () => {
                    cart = [];
                    updateCartUI();
                });
            }
        });
    }

    if (btnCheckout) {
        btnCheckout.addEventListener('click', function() {
            const total = getCartTotal();
            const paidStr = amountPaidEl.value.replace(/\D/g, '');
            const paid = paidStr ? parseFloat(paidStr) : 0;

            if (paid < total) {
                Notify.error('Jumlah bayar kurang dari total harga.', 'Pembayaran Kurang');
                return;
            }

            Notify.confirm('Proses transaksi ini sekarang?', async () => {
                btnCheckout.disabled = true;
                btnCheckout.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

                try {
                    const response = await fetch(STORE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            items: cart.map(item => ({ id: item.id, qty: item.qty })),
                            amount_paid: paid
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        const container = document.getElementById('receiptContainer');
                        if (container) {
                            container.innerHTML = result.receipt;
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Transaksi Berhasil!',
                            text: 'Apakah Anda ingin mencetak struk sekarang?',
                            showCancelButton: true,
                            confirmButtonText: '<i class="fas fa-print mr-2"></i> Cetak Struk',
                            cancelButtonText: 'Nanti Saja',
                            customClass: {
                                confirmButton: 'bg-emerald-600 text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-emerald-700 transition-colors outline-none mr-3',
                                cancelButton: 'bg-slate-100 text-slate-600 px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-200 transition-colors outline-none'
                            },
                            buttonsStyling: false
                        }).then((choice) => {
                            if (choice.isConfirmed) {
                                printReceipt();
                            }

                            cart = [];
                            amountPaidEl.value = '';
                            updateCartUI();
                            btnCheckout.disabled = false;
                            btnCheckout.innerHTML = '<i class="fas fa-check-circle"></i> Selesaikan Transaksi';
                        });
                    } else {
                        throw new Error(result.message || 'Terjadi kesalahan sistem.');
                    }
                } catch (error) {
                    Notify.error(error.message);
                    btnCheckout.disabled = false;
                    btnCheckout.innerHTML = '<i class="fas fa-check-circle"></i> Selesaikan Transaksi';
                }
            });
        });
    }
});

window.printReceipt = function() {
    const container = document.getElementById('receiptContainer');
    const contentDiv = container.querySelector('#receiptContent');
    
    if (!contentDiv) return;

    const content = contentDiv.innerHTML;
    
    let iframe = document.getElementById('print-iframe');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'print-iframe';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = '0';
        document.body.appendChild(iframe);
    }
    
    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Receipt</title>
            <style>
                @page { size: 58mm auto; margin: 4mm; }
                * { box-sizing: border-box; }
                body {
                    font-family: 'Courier New', Courier, monospace;
                    font-size: 10px;
                    width: 58mm;
                    margin: 0;
                    padding: 0;
                    color: #000;
                    background: #fff;
                }
                #receiptContent { width: 100%; }
            </style>
        </head>
        <body onload="window.print()">
            <div id="receiptContent">${content}</div>
        </body>
        </html>
    `);
    doc.close();
};
