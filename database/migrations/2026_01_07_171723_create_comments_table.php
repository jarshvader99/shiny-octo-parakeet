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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discussion_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Nested threading using parent_id
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');

            // Comment content
            $table->text('content');

            // Bill version snapshot - warn if bill changed since comment
            $table->foreignId('bill_version_id')->nullable()->constrained()->onDelete('set null');

            // Moderation
            $table->boolean('is_flagged')->default(false);
            $table->integer('flag_count')->default(0);
            $table->boolean('is_hidden')->default(false);
            $table->string('hidden_reason')->nullable();

            // Engagement metrics
            $table->integer('helpful_count')->default(0);

            // Nested set columns for efficient tree queries
            $table->integer('lft')->nullable();
            $table->integer('rgt')->nullable();
            $table->integer('depth')->default(0);

            $table->timestamps();
            $table->softDeletes(); // Preserve comment history

            // Indexes
            $table->index(['discussion_id', 'parent_id']);
            $table->index(['lft', 'rgt', 'depth']); // For nested set queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
