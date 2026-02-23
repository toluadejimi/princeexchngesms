<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('server_id')->constrained('api_servers')->cascadeOnDelete();
            $table->string('country_code', 10);
            $table->string('service_code');
            $table->string('phone_number', 20);
            $table->string('order_id')->nullable(); // provider activation/order id
            $table->decimal('cost', 12, 4);
            $table->string('status', 30)->default('pending'); // pending, active, completed, cancelled, expired
            $table->string('sms_code', 50)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['server_id', 'status']);
            $table->index('order_id');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
