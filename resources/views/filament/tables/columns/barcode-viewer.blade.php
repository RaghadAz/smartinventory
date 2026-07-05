{{-- resources/views/filament/tables/columns/barcode-viewer.blade.php --}}

@php
    $record = $getRecord();
    
    // ✅ توليد الباركود (أصغر حجم)
    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    $barcode = $generator->getBarcode(
        $record->barcode, 
        $generator::TYPE_CODE_128, 
        2,  // 🔧 العرض (أصغر من 5)
        60  // 🔧 الارتفاع (أصغر من 150)
    );
@endphp

<div style="
    text-align: center; 
    padding: 15px; 
    background: white;  /* 🔧 خلفية بيضة */
    border: 3px solid #333;  /* 🔧 حواف أقوى */
    border-radius: 8px;  /* 🔧 زوايا مدورة */
    display: inline-block;
    margin: 5px;
">
    {{-- ✅ الباركود أصغر --}}
    <img src="data:image/png;base64,{{ base64_encode($barcode) }}" 
         style="width: 200px; height: auto;">
    
    {{-- ✅ النص واضح --}}
    <p style="font-size: 16px; font-weight: bold; margin: 8px 0; color: #333;">
        {{ $record->barcode }}
    </p>
    
    {{-- ✅ اسم المنتج --}}
    <p style="font-size: 14px; color: #666; margin: 0;">
        {{ $record->name }}
    </p>
</div>