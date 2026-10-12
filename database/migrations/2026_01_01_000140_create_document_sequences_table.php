<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Concurrency-safe counters (MRN and other document numbers).
     *
     * A dedicated row is locked with SELECT ... FOR UPDATE inside a
     * transaction before incrementing, which is portable across MySQL 8 and
     * MariaDB and guaranteed collision-free under concurrent registration
     * (Phase 1 acceptance criterion).
     */
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name', 60); // e.g. mrn, invoice
            $table->unsignedBigInteger('next_value')->default(1);
            $table->timestamps();

            $table->unique(['facility_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
