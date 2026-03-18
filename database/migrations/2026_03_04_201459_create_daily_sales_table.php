<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_sales', function (Blueprint $table) {
            $table->id();

            $table->string('business_unit', 150);
            $table->date('operation_date');

            $table->decimal('alimentos', 12, 2)->default(0);
            $table->decimal('bebidas', 12, 2)->default(0);
            $table->decimal('otros', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['business_unit', 'operation_date']);
            $table->index('business_unit');
            $table->index('operation_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_sales');
    }
};
