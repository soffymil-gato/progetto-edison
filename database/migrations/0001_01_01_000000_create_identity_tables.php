<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Identity tables of the EdisonPortal database (platform-owned data only).
 *
 * Nothing here touches Edison's existing company database: customer accounts
 * reference the company customer record through a LOGICAL key
 * (customer_ref), never through a physical foreign key.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 254)->unique();
            $table->string('password')->nullable();
            $table->string('status', 20)->default('invited')->index();
            $table->dateTime('last_login_at')->nullable();
            $table->dateTime('password_changed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('portal_accounts', function (Blueprint $table) {
            $table->id();
            // Logical reference to the customer in the company database.
            // Mapping to be defined after the schema analysis.
            $table->string('customer_ref', 64)->index();
            $table->string('email', 254)->unique();
            $table->string('username', 64)->nullable();
            $table->string('display_name', 120);
            $table->string('password')->nullable();
            $table->string('status', 20)->default('invited')->index();
            $table->dateTime('last_login_at')->nullable();
            $table->dateTime('password_changed_at')->nullable();
            $table->timestamps();
        });

        // SQL Server allows a single NULL in a UNIQUE index: optional usernames
        // need a filtered unique index.
        if (DB::connection()->getDriverName() === 'sqlsrv') {
            DB::statement('CREATE UNIQUE INDEX portal_accounts_username_unique ON portal_accounts (username) WHERE username IS NOT NULL');
        } else {
            Schema::table('portal_accounts', fn (Blueprint $table) => $table->unique('username'));
        }

        foreach (['staff_password_reset_tokens', 'portal_password_reset_tokens'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->string('email', 254)->primary();
                $table->string('token');
                $table->dateTime('created_at')->nullable();
            });
        }

        Schema::create('account_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('account_type', 20);
            $table->unsignedBigInteger('account_id');
            // SHA-256 of the token sent by email: the token itself is never stored.
            $table->char('token_hash', 64)->unique();
            $table->dateTime('expires_at');
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->unsignedBigInteger('created_by_staff_id')->nullable();
            $table->timestamps();

            $table->index(['account_type', 'account_id']);
        });

        // One session table per area: sessions of customers and staff never mix,
        // and "log out other devices" can target a single area.
        foreach (['sessions', 'portal_sessions', 'admin_sessions'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->string('id')->primary();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_sessions');
        Schema::dropIfExists('portal_sessions');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('account_invitations');
        Schema::dropIfExists('portal_password_reset_tokens');
        Schema::dropIfExists('staff_password_reset_tokens');
        Schema::dropIfExists('portal_accounts');
        Schema::dropIfExists('staff_users');
    }
};
