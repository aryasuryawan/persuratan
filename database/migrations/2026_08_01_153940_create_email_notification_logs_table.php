<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('type'); // 'disposisi', 'follow_up', etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // disposisi_id, follow_up_id, etc.
            $table->string('recipient_type'); // 'user', 'freetext'
            $table->unsignedBigInteger('recipient_user_id')->nullable();
            $table->string('recipient_name');
            $table->string('recipient_email');
            $table->string('subject');
            $table->text('body')->nullable();
            $table->string('status'); // 'sent', 'failed', 'pending'
            $table->text('error_message')->nullable();
            $table->string('mailer_used')->nullable(); // 'smtp', 'mailersend', 'sendgrid', 'gmail', etc.
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'reference_id']);
            $table->index(['recipient_user_id']);
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_notification_logs');
    }
};