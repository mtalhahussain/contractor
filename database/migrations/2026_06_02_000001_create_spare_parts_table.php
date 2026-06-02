<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('part_number')->nullable()->unique();
            $table->string('category')->nullable();
            $table->string('unit', 30)->default('pcs');
            $table->decimal('current_stock', 12, 2)->default(0);
            $table->decimal('minimum_stock', 12, 2)->default(0);
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('machine_part_usages', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spare_part_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->string('usage_type', 30)->default('maintenance');
            $table->string('reference')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('part_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('spare_part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('machine_part_usage_id')->nullable()->constrained()->nullOnDelete();
            $table->string('movement_type', 30);
            $table->decimal('quantity', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reference')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['spare_part_id', 'date']);
            $table->index(['machine_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_stock_movements');
        Schema::dropIfExists('machine_part_usages');
        Schema::dropIfExists('spare_parts');
    }
};