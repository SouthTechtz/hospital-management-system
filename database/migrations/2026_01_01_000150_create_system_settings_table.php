<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 150)->unique();
            $table->text('value')->nullable();
            $table->string('group', 60)->default('general');
            $table->string('type', 20)->default('string'); // string|int|bool|json|secret
            $table->boolean('is_encrypted')->default(false);
            $table->string('verification_status', 40)->default('confirmed'); // confirmed|VERIFY_WITH_PROVIDER
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
