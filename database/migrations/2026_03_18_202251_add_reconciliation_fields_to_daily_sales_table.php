<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_sales', function (Blueprint $table) {
            $table->string('reconciliation_status')->nullable()->after('period_end');
            $table->json('reconciliation_data')->nullable()->after('reconciliation_status');
            $table->text('reconciliation_notes')->nullable()->after('reconciliation_data');
            $table->timestamp('reconciled_at')->nullable()->after('reconciliation_notes');
            $table->foreignId('reconciled_by')->nullable()->after('reconciled_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('daily_sales', function (Blueprint $table) {
            $table->dropForeign(['reconciled_by']);
            $table->dropColumn([
                'reconciliation_status',
                'reconciliation_data',
                'reconciliation_notes',
                'reconciled_at',
                'reconciled_by',
            ]);
        });
    }
};
