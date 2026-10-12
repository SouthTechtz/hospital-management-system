<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Object-level access: a clinician sees a patient record when an active
     * care assignment links them (or their department) to that patient, unless
     * break-glass is used (Part B).
     */
    public function up(): void
    {
        Schema::create('care_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('relationship', 30)->default('responsible'); // primary|responsible|consulting
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'released_at']);
            $table->index(['user_id', 'released_at']);
            $table->index(['department_id', 'released_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_assignments');
    }
};
