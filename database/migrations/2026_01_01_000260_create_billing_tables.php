<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cash and exemption billing (Part E, Phase 1). Itemised invoices with a
     * payer split, payments and time-boxed exemptions.
     */
    public function up(): void
    {
        Schema::create('service_tariffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('item_code', 40);
            $table->string('item_name');
            $table->string('category', 60)->default('clinical'); // registration|consultation|procedure|laboratory|pharmacy|other
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->string('currency', 3)->default('TZS');
            $table->boolean('is_exemptible')->default(false);
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->string('verification_status', 40)->default('confirmed');
            $table->timestamps();

            $table->unique(['facility_id', 'item_code']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('encounter_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number', 32);
            $table->string('payer_type', 20)->default('cash');
            $table->string('status', 20)->default('open');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('exemption_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['facility_id', 'invoice_number']);
            $table->index(['patient_id', 'status']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('item_code', 40)->nullable();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->boolean('is_exempt')->default(false);
            $table->string('exemption_category', 40)->nullable();
            $table->text('exemption_reason')->nullable();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['invoice_id']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method', 20);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['invoice_id']);
        });

        Schema::create('exemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('category', 40);
            $table->string('document_reference')->nullable();
            $table->text('reason')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exemptions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('service_tariffs');
    }
};
