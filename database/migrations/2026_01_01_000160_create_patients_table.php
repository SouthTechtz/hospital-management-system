<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master Patient Index (Part C). Identifying values (national ID, NIDA,
     * NHIF, phone) are encrypted at the application layer; deterministic
     * hashes are stored alongside so exact-match duplicate detection and
     * lookup remain possible without exposing the plaintext.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('mrn', 32);

            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('full_name_normalized', 255); // lowercased, accent-stripped, for duplicate scoring

            $table->string('sex', 12);
            $table->date('date_of_birth')->nullable();
            $table->boolean('birth_date_estimated')->default(false);
            $table->unsignedSmallInteger('estimated_age_years')->nullable();

            $table->text('national_id')->nullable();
            $table->string('national_id_hash', 64)->nullable();
            $table->text('nida_number')->nullable();
            $table->string('nida_hash', 64)->nullable();
            $table->timestamp('nida_verified_at')->nullable();

            $table->text('phone')->nullable();
            $table->string('phone_hash', 64)->nullable();
            $table->text('alt_phone')->nullable();
            $table->string('alt_phone_hash', 64)->nullable();
            $table->text('email')->nullable();

            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();
            $table->string('street')->nullable();

            $table->string('marital_status', 30)->nullable();
            $table->string('occupation')->nullable();
            $table->string('religion')->nullable();
            $table->string('nationality', 60)->default('Tanzanian');

            $table->string('payer_type', 20)->default('cash');
            $table->text('nhif_number')->nullable();
            $table->string('nhif_hash', 64)->nullable();
            $table->timestamp('nhif_verified_at')->nullable();

            $table->string('blood_group', 10)->nullable();
            $table->string('patient_category', 30)->default('general');
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();

            // Confirmed "no known allergies" is explicit, never implied by silence.
            $table->boolean('no_known_allergies_confirmed')->default(false);

            $table->string('merge_status', 20)->default('active'); // active|merged
            $table->foreignId('merged_into_patient_id')->nullable()
                ->constrained('patients')->nullOnDelete();

            $table->timestamp('deceased_at')->nullable();
            $table->string('death_certificate_no', 60)->nullable();

            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('registered_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['facility_id', 'mrn']);
            $table->index(['facility_id', 'last_name']);
            $table->index('full_name_normalized');
            $table->index('date_of_birth');
            $table->index('national_id_hash');
            $table->index('phone_hash');
            $table->index('nhif_hash');
        });

        Schema::create('patient_next_of_kin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('relationship', 60);
            $table->text('phone')->nullable();
            $table->string('phone_hash', 64)->nullable();
            $table->text('alt_phone')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('patient_id');
        });

        Schema::create('patient_allergies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('substance'); // drug name, class or food
            $table->string('substance_code', 60)->nullable(); // coded allergen where available
            $table->string('reaction')->nullable();
            $table->string('severity', 20)->default('moderate'); // mild|moderate|severe|life_threatening
            $table->string('status', 20)->default('active'); // active|resolved|entered_in_error
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['patient_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_allergies');
        Schema::dropIfExists('patient_next_of_kin');
        Schema::dropIfExists('patients');
    }
};
