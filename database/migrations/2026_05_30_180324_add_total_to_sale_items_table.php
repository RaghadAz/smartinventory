<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('sale_items', function (Blueprint $table) {
        // التأكد من أن الأعمدة غير موجودة قبل إضافتها لمنع حدوث خطأ "Column already exists"
        if (!Schema::hasColumn('sale_items', 'cost_price')) {
            $table->decimal('cost_price', 12, 2)->nullable();
        }
        if (!Schema::hasColumn('sale_items', 'line_profit')) {
            $table->decimal('line_profit', 12, 2)->nullable();
        }
        if (!Schema::hasColumn('sale_items', 'total')) {
            $table->decimal('total', 12, 2)->nullable();
        }
    });
}
    
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('total');
        });
    }
};
