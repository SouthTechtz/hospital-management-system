<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prescriptions are modelled as orders (Part E, Phase 1). A single order
     * header groups line items; order items reference the drug formulary for
     * medication orders and free text for other types. Orders and items are
     * versioned and voided, never hard-deleted (Part B).
     */
    public function up(): void
    {
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('generic_name');
            $table->string('brand_name')->nullable();
            $table->string('strength')->nullable();
            $table->string('form', 60)->nullable(); // tablet, syrup, injection
            $table->string('route', 40)->nullable();
            $table->string('unit', 30)->nullable(); // mg, ml, tab
            $table->string('atc_code', 20)->nullable();
            $table->boolean('is_controlled')->default(false);
            $table->boolean('is_essential')->default(false); // National Essential Medicines List
            $table->boolean('requires_prescription')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('generic_name');
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('encounter_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('consultation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('order_type', 20);
            $table->string('status', 20)->default('ordered');
            $table->string('priority', 10)->default('routine');
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->foreignId('previous_version_id')->nullable()
                ->constrained('orders')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->foreignId('ordered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ordered_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'status']);
            $table->index(['encounter_id', 'order_type']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 20)->default('medication');
            $table->foreignId('drug_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description')->nullable();
            $table->string('dose')->nullable();
            $table->string('dose_unit', 30)->nullable();
            $table->string('route', 40)->nullable();
            $table->string('frequency', 60)->nullable();
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('quantity_unit', 30)->nullable();
            $table->boolean('is_prn')->default(false);
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('version')->default(1);
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->index(['order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('drugs');
    }
};
