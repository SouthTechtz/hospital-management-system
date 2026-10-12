<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Granular RBAC: roles, permissions and the joins to users (Part B).
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique(); // stable slug, e.g. opd_doctor
            $table->string('display_name_en');
            $table->string('display_name_sw');
            $table->string('scope', 20)->default('facility'); // system|facility|department
            $table->string('description_en')->nullable();
            $table->string('description_sw')->nullable();
            $table->boolean('requires_mfa')->default(false);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique(); // module.action, e.g. patient.view
            $table->string('module', 60);
            $table->string('description_en')->nullable();
            $table->string('description_sw')->nullable();
            $table->timestamps();

            $table->index('module');
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['role_id', 'user_id']);
        });

        // Direct per-user grants and explicit denies. A deny always wins.
        Schema::create('permission_user', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('granted')->default(true);
            $table->timestamps();
            $table->primary(['permission_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
