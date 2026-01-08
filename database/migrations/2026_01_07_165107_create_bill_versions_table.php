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
        Schema::create('bill_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();

            // Version identification
            $table->string('version_code', 50); // IH, EH, IS, ES, ENR, "Engrossed Amendment House", etc.
            $table->string('version_name', 150); // "Introduced in House", "Engrossed in Senate", etc.
            $table->timestamp('published_at')->nullable();

            // Content
            $table->longText('full_text')->nullable();
            $table->string('text_url', 500)->nullable();
            $table->string('pdf_url', 500)->nullable();

            // Change tracking
            $table->string('text_hash', 64)->nullable(); // SHA-256 hash of content
            $table->text('diff_from_previous')->nullable(); // JSON diff or summary

            // Metadata
            $table->unsignedInteger('page_count')->nullable();
            $table->unsignedInteger('character_count')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['bill_id', 'published_at']);
            $table->index('version_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_versions');
    }
};
