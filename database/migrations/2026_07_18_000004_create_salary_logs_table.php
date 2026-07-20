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
        Schema::create('salary_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('log_date'); // First day of the month (e.g., 2026-07-01)
            $table->decimal('salary_amount', 12, 2);
            $table->decimal('total_advances', 12, 2)->default(0);
            $table->decimal('net_payable', 12, 2); // salary_amount - total_advances
            $table->integer('advance_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'log_date']);
            $table->unique(['employee_id', 'log_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_logs');
    }
};
