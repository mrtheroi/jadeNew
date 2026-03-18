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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_type_id')->nullable()->constrained('expense_types')->nullOnDelete();
            $table->string('business_unit', 150);
            $table->string('expense_name', 150);
            $table->string('provider_name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('business_unit');
            $table->index('expense_type_id');
            $table->index(['business_unit', 'expense_type_id']);

            $table->unique(['business_unit', 'expense_type_id', 'expense_name', 'provider_name']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
