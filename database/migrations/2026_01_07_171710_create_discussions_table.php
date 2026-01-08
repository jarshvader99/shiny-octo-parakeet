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
        Schema::create('discussions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->onDelete('cascade');

            // Discussion section type
            $table->enum('section', [
                'key_questions',
                'arguments_for',
                'arguments_against',
                'impact_analysis',
                'general'
            ]);

            // Optional: Pinned/featured discussions
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);

            // Metadata
            $table->integer('comment_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();

            $table->timestamps();

            // Index for efficient querying
            $table->index(['bill_id', 'section']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discussions');
    }
};
