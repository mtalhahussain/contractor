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
        Schema::table('salary_logs', function (Blueprint $table) {
            $table->decimal('leave_deduction', 10, 2)->default(0)->after('total_advances');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_logs', function (Blueprint $table) {
            $table->dropColumn('leave_deduction');
        });
    }
};
