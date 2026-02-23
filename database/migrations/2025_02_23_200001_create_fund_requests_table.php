<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 4);
            $table->string('ref_id', 64)->unique()->index();
            $table->string('order_id', 64)->nullable()->index();
            $table->string('session_id', 64)->nullable();
            $table->string('account_no', 20)->nullable();
            $table->string('type', 20); // instant, manual
            $table->string('status', 20)->default('pending'); // pending, completed, failed, cancelled
            $table->string('receipt_path')->nullable(); // for manual: uploaded receipt
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_requests');
    }
};
