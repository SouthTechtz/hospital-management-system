<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two credential stores (Part B):
     *  1. outbound_credentials - secrets the HMS uses to call national systems.
     *  2. inbound_api_keys - keys issued to other systems calling the HMS.
     */
    public function up(): void
    {
        Schema::create('outbound_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 60); // nhif|dhis2|moh|gepg|hfr|nida
            $table->string('label');
            $table->text('username')->nullable(); // encrypted at rest
            $table->text('secret')->nullable(); // encrypted at rest
            $table->text('extra')->nullable(); // encrypted JSON (tokens, client ids)
            $table->string('environment', 20)->default('sandbox'); // sandbox|production
            $table->string('base_url')->nullable();
            $table->string('verification_status', 40)->default('VERIFY_WITH_PROVIDER');
            $table->boolean('is_active')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['provider', 'environment']);
        });

        Schema::create('inbound_api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('key_prefix', 16)->index();
            $table->string('key_hash', 64)->unique(); // SHA-256, plaintext shown once only
            $table->json('scopes');
            $table->unsignedInteger('rate_limit_per_minute')->default(60);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedBigInteger('request_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('inbound_api_key_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_api_key_id')->constrained()->cascadeOnDelete();
            $table->string('method', 10);
            $table->string('path');
            $table->unsignedSmallInteger('status_code');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['inbound_api_key_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_api_key_usage');
        Schema::dropIfExists('inbound_api_keys');
        Schema::dropIfExists('outbound_credentials');
    }
};
