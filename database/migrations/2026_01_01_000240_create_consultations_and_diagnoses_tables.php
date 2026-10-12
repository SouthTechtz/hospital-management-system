<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ICD-10 is the system of record for diagnosis coding (Part A). ICD-11
     * may be stored as an optional secondary mapping on the diagnoses table.
     */
    public function up(): void
    {
        Schema::create('icd10_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 12)->unique();
            $table->string('description');
            $table->string('chapter', 120)->nullable();
            $table->string('category', 120)->nullable();
            $table->boolean('is_billable')->default(false);
            $table->boolean('is_common_tanzania')->default(false);
            $table->text('keywords')->nullable();
            $table->timestamps();

            $table->fullText(['description', 'keywords']);
        });

        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('encounter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->text('history')->nullable();
            $table->text('examination')->nullable();
            $table->text('plan')->nullable();
            $table->string('disposition', 40)->nullable(); // discharge|admit|refer|follow_up
            $table->date('follow_up_date')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->foreignId('previous_version_id')->nullable()
                ->constrained('consultations')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['encounter_id', 'voided_at']);
            $table->index(['patient_id', 'created_at']);
        });

        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('encounter_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('consultation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('icd10_code_id')->nullable()->constrained()->nullOnDelete();
            $table->string('icd10_code', 12)->nullable();
            $table->string('icd10_description')->nullable();
            $table->string('icd11_code', 20)->nullable();
            $table->string('type', 20)->default('primary');
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->foreignId('previous_version_id')->nullable()
                ->constrained('diagnoses')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->foreignId('diagnosed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diagnosed_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'voided_at']);
            $table->index(['encounter_id', 'voided_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
        Schema::dropIfExists('consultations');
        Schema::dropIfExists('icd10_codes');
    }
};
