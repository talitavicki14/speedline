window.printReceipt = function() {
    const content = document.getElementById('receiptContent').innerHTML;
    
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
