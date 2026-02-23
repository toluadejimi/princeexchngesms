<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained('api_servers')->cascadeOnDelete();
            $table->string('country_code', 10)->nullable(); // null = default/USA for usa_only
            $table->string('service_code');
            $table->decimal('price', 12, 4);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['server_id', 'country_code', 'service_code'], 'server_country_service_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_pricing');
    }
};
