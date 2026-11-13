<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only audit log.
 *
 * Protection layers:
 * 1. the application model refuses updates and deletes;
 * 2. on SQL Server an INSTEAD OF trigger rejects UPDATE/DELETE statements
 *    (the change is never applied and an error is raised);
 * 3. in production the application login must also be denied UPDATE/DELETE
 *    on this table (see docs/security/database-permissions.md).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('occurred_at', 6)->index();
            $table->string('actor_type', 20);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_label', 254)->nullable();
            $table->string('action', 100)->index();
            $table->string('subject_type', 50)->nullable();
            $table->string('subject_id', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->text('context')->nullable();

            $table->index(['actor_type', 'actor_id']);
            $table->index(['subject_type', 'subject_id']);
        });

        if (DB::connection()->getDriverName() === 'sqlsrv') {
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER audit_logs_append_only
                ON audit_logs
                INSTEAD OF UPDATE, DELETE
                AS
                BEGIN
                    -- The statement is not applied. RAISERROR (unlike THROW) reports the
                    -- error without dooming the caller's transaction.
                    RAISERROR (N'audit_logs is append-only.', 16, 1);
                END
                SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
