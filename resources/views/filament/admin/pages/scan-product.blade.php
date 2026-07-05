<x-filament-panels::page>
    <div class="space-y-6" dir="rtl">
        
        <div class="p-4 bg-gray-800 rounded-xl border border-green-500 shadow-lg">
            <input type="text" 
                   wire:model.live.debounce.300ms="barcode" 
                   placeholder="امسح الباركود أو اكتب الرقم هنا..." 
                   class="w-full p-4 bg-gray-900 text-white rounded-lg border border-gray-700 focus:border-green-500" 
                   autofocus>
        </div>

        <div class="p-6 bg-gray-800 rounded-xl border border-green-500 shadow-lg">
            @if($lastScannedProduct)
                <div class="text-center">
                    <h3 class="text-2xl font-bold text-white mb-2">{{ $lastScannedProduct->name }}</h3>
                    <p class="text-lg text-green-400">السعر: {{ number_format($lastScannedProduct->price, 2) }} ل.س</p>
                </div>
            @else
                <div class="text-center text-gray-400 py-4">
                    <p>بانتظار مسح منتج جديد...</p>
                </div>
            @endif

            <hr class="my-6 border-gray-700">

            <div class="text-center">
                <h2 class="text-3xl font-bold text-white">
                    الإجمالي: {{ number_format($totalPrice, 2) }} ل.س
                </h2>
            </div>
            
            <div class="flex justify-center mt-6">
                <button wire:click="resetScanner" 
                        class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition duration-200">
                    مسح منتج آخر
                </button>
            </div>
        </div>

        @if(!$lastScannedProduct)
            <div class="bg-black rounded-lg overflow-hidden border border-gray-700">
                <div id="reader" class="w-full"></div>
            </div>

            <script src="https://unpkg.com/html5-qrcode"></script>
            <script>
                // إعداد الكاميرا
                const html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
                    fps: 10, 
                    qrbox: { width: 250, height: 150 } 
                }, false);

                html5QrcodeScanner.render((decodedText) => {
                    // إرسال الكود للدالة في الكلاس
                    @this.call('searchProduct', decodedText);
                    html5QrcodeScanner.clear();
                }, (error) => {});

                // مستمع لطلب إعادة تحميل الصفحة لتفعيل الكاميرا من جديد
                window.addEventListener('refresh-page', () => {
                    location.reload();
                });
            </script>
        @endif
    </div>
</x-filament-panels::page>