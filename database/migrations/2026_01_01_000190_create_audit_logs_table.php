<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only audit log (Part B). There is no updated_at and the database
     * user is not granted UPDATE or DELETE; triggers below additionally reject
     * any attempt to modify a written row.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('actor_name')->nullable();
            $table->string('actor_role')->nullable();
            $table->string('action', 40);
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('device')->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['patient_id', 'occurred_at']);
            $table->index(['user_id', 'occurred_at']);
            $table->index(['facility_id', 'action', 'occurred_at']);
        });

        // Application-level immutability. Works on MySQL 8 and MariaDB.
        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            \Illuminate\Support\Facades\DB::unprepared('
                CREATE TRIGGER audit_logs_prevent_update BEFORE UPDATE ON audit_logs
                FOR EACH ROW SIGNAL SQLSTATE \'45000\'
                SET MESSAGE_TEXT = \'audit_logs is append-only\';
            ');
            \Illuminate\Support\Facades\DB::unprepared('
                CREATE TRIGGER audit_logs_prevent_delete BEFORE DELETE ON audit_logs
                FOR EACH ROW SIGNAL SQLSTATE \'45000\'
                SET MESSAGE_TEXT = \'audit_logs is append-only\';
            ');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            \Illuminate\Support\Facades\DB::unprepared('DROP TRIGGER IF EXISTS audit_logs_prevent_update');
            \Illuminate\Support\Facades\DB::unprepared('DROP TRIGGER IF EXISTS audit_logs_prevent_delete');
        }
        Schema::dropIfExists('audit_logs');
    }
};
