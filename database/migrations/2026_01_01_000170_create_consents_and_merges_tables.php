<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('purpose', 40); // treatment|data_sharing|research|audit
            $table->boolean('granted')->default(true);
            $table->string('method', 40)->default('verbal'); // verbal|written|digital
            $table->string('evidence_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->text('withdrawal_reason')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'purpose']);
        });

        Schema::create('patient_merges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('source_patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('target_patient_id')->constrained('patients')->restrictOnDelete();
            $table->string('reason');
            $table->json('snapshot'); // full identifying snapshot of the source record
            $table->foreignId('merged_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('merged_at');
            $table->foreignId('unmerged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('unmerged_at')->nullable();
            $table->text('unmerge_reason')->nullable();
            $table->timestamps();

            $table->index(['source_patient_id', 'unmerged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_merges');
        Schema::dropIfExists('consents');
    }
};
