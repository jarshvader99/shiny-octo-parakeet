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
        Schema::create('bill_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bill_id')->constrained()->onDelete('cascade');

            // Notification preferences
            $table->boolean('notify_on_amendment')->default(true);
            $table->boolean('notify_on_vote')->default(true);
            $table->boolean('notify_on_status_change')->default(true);
            $table->boolean('notify_on_new_discussion')->default(false);

            // Track when user started following
            $table->timestamp('followed_at')->useCurrent();

            // Track last notification sent to avoid spam
            $table->timestamp('last_notified_at')->nullable();

            $table->timestamps();

            // User can only follow a bill once
            $table->unique(['user_id', 'bill_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_followers');
    }
};
