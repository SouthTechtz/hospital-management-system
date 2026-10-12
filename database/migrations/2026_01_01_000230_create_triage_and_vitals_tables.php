<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('triage_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('encounter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('category', 20);
            $table->text('chief_complaint')->nullable();
            $table->text('notes')->nullable();
            $table->string('version', 10)->default('1');
            $table->foreignId('previous_version_id')->nullable()
                ->constrained('triage_assessments')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->foreignId('triaged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('triaged_at')->nullable();
            $table->timestamps();

            $table->index(['encounter_id', 'voided_at']);
            $table->index(['patient_id', 'triaged_at']);
        });

        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('encounter_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('temperature_c', 4, 1)->nullable();
            $table->unsignedSmallInteger('pulse')->nullable();
            $table->unsignedSmallInteger('respiratory_rate')->nullable();
            $table->unsignedSmallInteger('systolic')->nullable();
            $table->unsignedSmallInteger('diastolic')->nullable();
            $table->unsignedSmallInteger('spo2')->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->unsignedSmallInteger('muac_mm')->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            $table->unsignedTinyInteger('pain_score')->nullable();
            $table->unsignedTinyInteger('gcs')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'recorded_at']);
            $table->index(['encounter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
        Schema::dropIfExists('triage_assessments');
    }
};
