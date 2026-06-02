<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('unit', 20)->default('liters');
            $table->decimal('current_stock', 12, 2)->default(0);
            $table->decimal('minimum_stock', 12, 2)->default(0);
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fuel_issues', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('fuel_stock_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained()->nullOnDelete();
            $table->string('consumer_type', 30);
            $table->string('consumer_name')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->string('reference')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['date', 'fuel_stock_id']);
            $table->index(['date', 'machine_id']);
        });

        Schema::create('fuel_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('fuel_stock_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fuel_issue_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained()->nullOnDelete();
            $table->string('movement_type', 30);
            $table->decimal('quantity', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reference')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['fuel_stock_id', 'date']);
            $table->index(['machine_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_stock_movements');
        Schema::dropIfExists('fuel_issues');
        Schema::dropIfExists('fuel_stocks');
    }
};