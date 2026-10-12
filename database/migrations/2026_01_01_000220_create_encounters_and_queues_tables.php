<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code', 32);
            $table->string('type', 30)->default('clinical'); // triage|clinical|laboratory|radiology|pharmacy|cashier
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['facility_id', 'code']);
        });

        Schema::create('encounters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('visit_number', 32);
            $table->string('encounter_type', 30);
            $table->string('status', 20)->default('open');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('clinician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('location')->nullable();
            $table->string('payment_type', 20)->default('cash');
            $table->foreignId('attending_encounter_id')->nullable()
                ->constrained('encounters')->nullOnDelete(); // link follow-on encounters
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['facility_id', 'visit_number']);
            $table->index(['patient_id', 'status']);
            $table->index(['facility_id', 'encounter_type', 'status']);
        });

        Schema::create('queue_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('queue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('encounter_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('triage_category', 20)->nullable();
            $table->unsignedSmallInteger('priority')->default(3);
            $table->string('status', 20)->default('waiting');
            $table->timestamp('added_at')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['queue_id', 'status', 'priority']);
            $table->index(['encounter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_entries');
        Schema::dropIfExists('encounters');
        Schema::dropIfExists('queues');
    }
};
