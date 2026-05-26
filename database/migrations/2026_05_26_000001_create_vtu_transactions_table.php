<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vtu_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('status', 30)->default('pending');
            $table->string('reference', 80)->unique();
            $table->string('provider_reference', 120)->nullable()->index();
            $table->string('service_id', 80)->nullable();
            $table->string('variation_code', 120)->nullable();
            $table->string('recipient', 120);
            $table->decimal('amount', 12, 4);
            $table->decimal('wallet_debit', 12, 4);
            $table->string('customer_name')->nullable();
            $table->text('token')->nullable();
            $table->text('message')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vtu_transactions');
    }
};
