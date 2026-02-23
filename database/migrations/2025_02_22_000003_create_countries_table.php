<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained('api_servers')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 10); // e.g. US, 187 for sms-activate
            $table->string('provider_country_id')->nullable(); // provider's internal id
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['server_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
