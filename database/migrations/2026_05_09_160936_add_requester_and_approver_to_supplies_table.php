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
        Schema::table('supplies', function (Blueprint $table) {
            $table->foreignId('requester_id')
                ->nullable()
                ->after('purchase_order_id')
                ->constrained('employees')
                ->nullOnDelete();

            $table->foreignId('approver_id')
                ->nullable()
                ->after('requester_id')
                ->constrained('employees')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requester_id');
            $table->dropConstrainedForeignId('approver_id');
        });
    }
};
