<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('break_glass_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('reason_code', 60);
            $table->text('reason_text');
            $table->json('scope')->nullable();
            $table->timestamp('granted_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->boolean('privacy_officer_notified')->default(false);
            $table->timestamp('privacy_officer_notified_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('review_outcome', 40)->nullable(); // appropriate|inappropriate|needs_followup
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'patient_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('break_glass_accesses');
    }
};
