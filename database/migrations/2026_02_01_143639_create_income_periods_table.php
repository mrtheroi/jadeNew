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
        Schema::create('income_periods', function (Blueprint $table) {
            $table->id();

            $table->string('business_unit')->index(); // Jade | Fuego Ambar | KIN
            $table->string('period_key')->index();    // YYYY-MM (ej. 2026-01)

            $table->decimal('income_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->unique(['business_unit', 'period_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_periods');
    }
};
