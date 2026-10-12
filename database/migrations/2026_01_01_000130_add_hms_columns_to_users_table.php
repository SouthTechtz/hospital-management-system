<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('facility_id')->nullable()->after('id')
                ->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('facility_id')
                ->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('primary_role_id')->nullable()->after('department_id')
                ->constrained('roles')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('preferred_locale')
                ->constrained('users')->nullOnDelete();

            $table->string('job_title')->nullable()->after('last_name');
            $table->string('registration_no', 60)->nullable()->after('job_title'); // professional council number

            // MFA (TOTP). The secret is encrypted at the application layer.
            $table->boolean('mfa_enabled')->default(false);
            $table->text('mfa_secret')->nullable();
            $table->timestamp('mfa_confirmed_at')->nullable();

            // Lockout with backoff.
            $table->unsignedSmallInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();

            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamp('deactivated_at')->nullable();

            $table->index('facility_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('facility_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('primary_role_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn([
                'job_title', 'registration_no', 'mfa_enabled', 'mfa_secret',
                'mfa_confirmed_at', 'failed_login_attempts', 'locked_until',
                'last_login_at', 'last_login_ip', 'deactivated_at',
            ]);
        });
    }
};
