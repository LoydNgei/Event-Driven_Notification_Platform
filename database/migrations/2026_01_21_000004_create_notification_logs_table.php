<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notification_rule_id')->constrained()->cascadeOnDelete();
            $table->enum('channel', ['email', 'sms', 'slack']);
            $table->json('payload');
            $table->string('recipient')->nullable();
            $table->enum('status', ['pending', 'processing', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
            $table->index(['event_source_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
