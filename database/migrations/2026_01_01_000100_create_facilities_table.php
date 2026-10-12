<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('short_name', 100)->nullable();
            $table->string('type', 50)->default('regional_referral_hospital');
            $table->string('hfr_code', 50)->nullable(); // Health Facility Registry code (configuration)
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('physical_address')->nullable();
            $table->string('postal_address')->nullable();
            $table->text('phone')->nullable();
            $table->text('email')->nullable();
            $table->string('timezone', 64)->default('Africa/Dar_es_Salaam');
            $table->string('currency', 3)->default('TZS');
            $table->string('logo_path')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
