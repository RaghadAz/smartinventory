<x-filament-panels::page>
    <div class="space-y-6" dir="rtl">

        {{-- حقل إدخال يدوي للباركود --}}
        <div class="p-4 bg-gray-800 rounded-xl border border-green-500 shadow-lg">
            <input type="text" wire:model.defer="barcode" placeholder="اكتب الباركود هنا..."
                wire:keydown.enter="searchProduct(barcode)"
                class="w-full text-center text-xl p-3 rounded bg-white text-black" />
        </div>

        {{-- عرض آخر منتج + الإجمالي --}}
        <div class="p-6 bg-gray-800 rounded-xl border border-green-500 shadow-lg">
            @if ($lastScannedProduct)
                <div class="text-center">
                    <h3 class="text-2xl font-bold text-white mb-2">
                        {{ $lastScannedProduct->name }}
                    </h3>
                    <p class="text-lg text-green-400">
                        السعر: {{ number_format($lastScannedProduct->price, 2) }} ل.س
                    </p>
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

            {{-- زر إتمام البيع --}}
            <div class="flex justify-center mt-6">
                <button wire:click="completeSale"
                    class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg transition duration-200">
                    إتمام البيع
                </button>
            </div>

            {{-- زر إعادة التعيين --}}
            <div class="flex justify-center mt-4">
                <button wire:click="resetScanner"
                    class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition duration-200">
                    مسح منتج آخر
                </button>
            </div>
        </div>

        {{-- الكاميرا تعمل فقط إذا لا يوجد منتج ممسوح --}}
        <div class="bg-black rounded-lg overflow-hidden border border-gray-700">
            <div id="reader" class="w-full"></div>
        </div>

        {{-- مكتبة QuaggaJS --}}
        <script src="https://unpkg.com/html5-qrcode"></script>

        <script>
            const html5QrcodeScanner = new Html5QrcodeScanner("reader", {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 150
                }
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
            document.addEventListener('DOMContentLoaded', function() {
                Quagga.init({
                    inputStream: {
                        name: "Live",
                        type: "LiveStream",
                        target: document.querySelector('#quagga-reader'),
                        constraints: {
                            facingMode: "environment"
                        }
                    },
                    decoder: {
                        readers: [
                            "code_128_reader",
                            "code_39_reader",
                            "ean_reader",
                            "ean_8_reader",
                            "upc_reader",
                            "upc_e_reader"
                        ]
                    }
                }, function(err) {
                    if (err) {
                        console.error(err);
                        return;
                    }
                    Quagga.start();
                });

                Quagga.onDetected(function(result) {
                    let code = result.codeResult.code;

                    // إرسال الباركود إلى Livewire
                    @this.call('searchProduct', code);

                    // إيقاف الكاميرا بعد القراءة
                    Quagga.stop();
                });

                // إعادة تشغيل الكاميرا بعد resetScanner
                window.addEventListener('refresh-page', () => {
                    Quagga.start();
                });
            });
        </script>
    </div>
</x-filament-panels::page>
