<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Requests sent through the public contact form.
 * Data minimisation: no IP address or user agent is stored.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('company', 160)->nullable();
            $table->string('email', 254);
            $table->string('phone', 40)->nullable();
            $table->string('subject', 160);
            $table->text('message');
            $table->dateTime('privacy_accepted_at');
            $table->string('status', 20)->default('new')->index();
            $table->unsignedBigInteger('handled_by_staff_id')->nullable();
            $table->dateTime('handled_at')->nullable();
            $table->string('handling_note', 1000)->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_requests');
    }
};
