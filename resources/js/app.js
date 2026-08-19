import Alpine from 'alpinejs';
window.Alpine = Alpine;

window.orderHistoryPage = function (config = {}) {
    return {
        orders: config.orders || [],
        gstSettings: config.gstSettings || {},
        activeOrderId: null,

        get selectedOrder() {
            return this.orders.find((order) => Number(order.id) === Number(this.activeOrderId)) || null;
        },

        openDetail(orderId) {
            this.activeOrderId = orderId;
        },

        closeDetail() {
            this.activeOrderId = null;
        },

        money(value) {
            return 'Rs. ' + Number(value || 0).toFixed(2);
        },

        statusClass(status) {
            return {
                preparing: 'bg-orange/10 text-orange border border-orange/10',
                ready: 'bg-teal/10 text-teal border border-teal/10',
                served: 'bg-success/10 text-success border border-success/10',
                completed: 'bg-success/10 text-success border border-success/10',
                cancelled: 'bg-danger/10 text-danger border border-danger/10',
            }[status] || 'bg-card-tint text-muted border border-border';
        },

        paymentClass(status) {
            return {
                paid: 'bg-success/10 text-success border border-success/10',
                pending: 'bg-orange/10 text-orange border border-orange/10',
                unpaid: 'bg-danger/10 text-danger border border-danger/10',
                refunded: 'bg-slate-100 text-slate-600 border border-slate-200',
            }[status] || 'bg-card-tint text-muted border border-border';
        },

        receiptItems(order) {
            if (!order) {
                return [];
            }

            return (order.items || []).filter((item) => item.status !== 'cancelled');
        },

        receiptTotals(order) {
            const gst = this.gstSettings || {};
            const hasGst = Boolean(gst.enabled);
            const subtotal = Number(order?.subtotal || 0);
            const discount = Number(order?.discount || 0);
            const tax = hasGst ? Number(order?.tax || 0) : 0;
            const taxableAfterDiscount = Math.max(0, subtotal - discount);
            const total = hasGst ? Number(order?.total || 0) : taxableAfterDiscount;
            const cgstRate = Number(gst.cgstRate || 0);
            const sgstRate = Number(gst.sgstRate || 0);
            const totalTaxRate = cgstRate + sgstRate;
            const cgstAmount = hasGst && totalTaxRate > 0 ? tax * (cgstRate / totalTaxRate) : (hasGst ? tax / 2 : 0);
            const sgstAmount = hasGst && totalTaxRate > 0 ? tax * (sgstRate / totalTaxRate) : (hasGst ? tax / 2 : 0);

            return {
                hasGst,
                subtotal,
                discount,
                tax,
                taxableAfterDiscount,
                total,
                cgstRate,
                sgstRate,
                cgstAmount,
                sgstAmount,
            };
        },

        escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        buildReceiptHtml(order) {
            if (!order) {
                return '';
            }

            const gst = this.gstSettings || {};
            const totals = this.receiptTotals(order);
            const items = this.receiptItems(order);
            const money = (value) => 'Rs. ' + Number(value || 0).toFixed(2);
            const escape = (value) => this.escapeHtml(value);
            const lines = [];

            if (gst.address) {
                lines.push(gst.address);
            }

            if (gst.pincode) {
                lines.push('PIN: ' + gst.pincode);
            }

            if (totals.hasGst && gst.gstNo) {
                lines.push('GSTIN: ' + gst.gstNo);
            }

            const itemRows = items.map((item) => {
                const itemHtml = [];

                itemHtml.push('<div class="item">');
                itemHtml.push('<div class="item-name">' + escape(item.qty + 'x ' + item.displayName) + '</div>');
                itemHtml.push('<div class="row"><span>' + money(item.unitPrice) + ' x ' + item.qty + '</span><span>' + money(item.lineSubtotal) + '</span></div>');

                if (totals.hasGst && Number(item.tax || 0) > 0) {
                    itemHtml.push('<div class="row"><span>GST</span><span>' + money(item.tax) + '</span></div>');
                }

                if (item.specialInstructions) {
                    itemHtml.push('<div class="muted">Note: ' + escape(item.specialInstructions) + '</div>');
                }

                itemHtml.push('</div>');

                return itemHtml.join('');
            }).join('');

            const summaryRows = [];
            summaryRows.push('<div class="section">' + (totals.hasGst ? 'GST BILL SUMMARY' : 'BILL SUMMARY') + '</div>');
            summaryRows.push('<div class="row"><span>Subtotal:</span><span>' + money(totals.subtotal) + '</span></div>');

            if (totals.discount > 0) {
                summaryRows.push('<div class="row"><span>Discount:</span><span>- ' + money(totals.discount) + '</span></div>');
            }

            if (totals.hasGst) {
                summaryRows.push('<div class="row"><span>CGST (' + Number(totals.cgstRate).toFixed(2) + '%):</span><span>' + money(totals.cgstAmount) + '</span></div>');
                summaryRows.push('<div class="row"><span>SGST (' + Number(totals.sgstRate).toFixed(2) + '%):</span><span>' + money(totals.sgstAmount) + '</span></div>');
                summaryRows.push('<div class="row"><span>Total GST:</span><span>' + money(totals.tax) + '</span></div>');
            }

            summaryRows.push('<div class="row total"><span>Total:</span><span>' + money(totals.total) + '</span></div>');

            const html = [
                '<!doctype html>',
                '<html>',
                '<head>',
                '<meta charset="utf-8">',
                '<title>' + escape(order.orderNumber) + ' Bill</title>',
                '<style>',
                'body{font-family:Courier,monospace;width:76mm;margin:0 auto;padding:10px 5px;font-size:10.5px;line-height:1.35;color:#000}',
                '.center{text-align:center}',
                '.divider{border-top:1px dashed #000;margin:7px 0}',
                '.bold{font-weight:bold}',
                '.muted{font-size:9.5px}',
                '.section{margin:7px 0 4px;font-weight:bold;text-align:center}',
                '.row{display:flex;justify-content:space-between;gap:8px;margin-bottom:4px}',
                '.row span:first-child{max-width:48mm}',
                '.row span:last-child{text-align:right;white-space:nowrap}',
                '.item{margin:6px 0}',
                '.item-name{font-weight:bold;white-space:pre-wrap}',
                '.total{font-size:13px;border-top:1px solid #000;padding-top:5px;margin-top:5px}',
                '</style>',
                '</head>',
                '<body>',
                '<div class="center">',
                '<div class="bold">' + escape(gst.brandName || 'Business') + '</div>',
                lines.map((line) => '<div>' + escape(line) + '</div>').join(''),
                '</div>',
                '<div class="divider"></div>',
                '<div class="row"><span>Bill No:</span><span>' + escape(order.displayId) + '</span></div>',
                '<div class="row"><span>Order No:</span><span>' + escape(order.orderNumber) + '</span></div>',
                '<div class="row"><span>Date:</span><span>' + escape(order.date + ' ' + order.time) + '</span></div>',
                '<div class="row"><span>Location:</span><span>' + escape(order.location) + '</span></div>',
                '<div class="row"><span>Customer:</span><span>' + escape(order.customerName) + '</span></div>',
            ];

            if (order.customerPhone && order.customerPhone !== 'N/A') {
                html.push('<div class="row"><span>Phone:</span><span>' + escape(order.customerPhone) + '</span></div>');
            }

            html.push('<div class="divider"></div>');
            html.push('<div class="section">ITEM DETAILS</div>');
            html.push(itemRows);
            html.push('<div class="divider"></div>');
            html.push(summaryRows.join(''));
            html.push('<div class="divider"></div>');
            html.push('<div class="row"><span>Payment:</span><span>' + escape(order.paymentLabel) + '</span></div>');
            html.push('<div class="row"><span>Status:</span><span>' + escape(order.statusLabel) + '</span></div>');

            if (order.note) {
                html.push('<div class="divider"></div>');
                html.push('<div class="muted">Instructions: ' + escape(order.note) + '</div>');
            }

            html.push('<div class="divider"></div>');
            html.push('<div class="center bold">Thank you</div>');
            html.push('</body>');
            html.push('</html>');

            return html.join('');
        },

        printReceipt() {
            if (!this.selectedOrder) {
                return;
            }

            const receiptWindow = window.open('', '_blank', 'width=380,height=680');

            if (!receiptWindow) {
                return;
            }

            receiptWindow.document.open();
            receiptWindow.document.write(this.buildReceiptHtml(this.selectedOrder));
            receiptWindow.document.close();
            receiptWindow.focus();

            setTimeout(() => {
                receiptWindow.print();
            }, 250);
        },

        downloadReceipt() {
            if (!this.selectedOrder) {
                return;
            }

            const blob = new Blob([this.buildReceiptHtml(this.selectedOrder)], { type: 'text/html;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = (this.selectedOrder.orderNumber || this.selectedOrder.displayId) + '-bill.html';
            document.body.appendChild(link);
            link.click();
            link.remove();

            setTimeout(() => URL.revokeObjectURL(url), 1000);
        },
    };
};

const createNotificationSound = () => {
    let audio = null;
    let unlocked = false;
    let lastPlayedAt = 0;

    const getAudio = () => {
        if (!audio) {
            audio = new Audio('/sounds/notification.mp3');
            audio.preload = 'auto';
            audio.volume = 0.75;
        }

        return audio;
    };

    const unlock = () => {
        const sound = getAudio();
        unlocked = true;
        sound.muted = true;

        const playPromise = sound.play();
        if (playPromise?.then) {
            playPromise
                .then(() => {
                    sound.pause();
                    sound.currentTime = 0;
                    sound.muted = false;
                })
                .catch(() => {
                    sound.muted = false;
                    sound.load();
                });
            return;
        }

        sound.muted = false;
    };

    ['click', 'keydown', 'touchstart'].forEach((eventName) => {
        window.addEventListener(eventName, unlock, { once: true, passive: true });
    });

    return {
        play() {
            const now = Date.now();
            if (now - lastPlayedAt < 1500) {
                return;
            }

            lastPlayedAt = now;
            const sound = getAudio();
            sound.currentTime = 0;

            const playPromise = sound.play();
            if (playPromise?.catch) {
                playPromise.catch(() => {
                    unlocked = false;
                });
            }
        },

        prime() {
            if (unlocked) {
                getAudio().load();
            }
        },
    };
};

window.notificationSound = window.notificationSound || createNotificationSound();

Alpine.start();
