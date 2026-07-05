<div id="reader" style="width: 100%;"></div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    function onScanSuccess(decodedText, decodedResult) {
        // هذا هو الجزء الأهم: إرسال القراءة إلى حقل scan_barcode
        Livewire.dispatch('barcodeScanned', { barcode: decodedText });
    }
    let html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
    html5QrcodeScanner.render(onScanSuccess);
</script>