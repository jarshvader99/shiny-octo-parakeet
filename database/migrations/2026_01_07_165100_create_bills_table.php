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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();

            // Bill identification
            $table->unsignedSmallInteger('congress_number'); // e.g., 119 for 119th Congress
            $table->enum('chamber', ['house', 'senate', 'joint']);
            $table->string('bill_type', 10); // HR, S, HJRES, SJRES, etc.
            $table->unsignedInteger('bill_number');

            // Bill content
            $table->text('title'); // Some bills have very long titles
            $table->string('short_title', 255)->nullable();
            $table->text('summary')->nullable();
            $table->text('constitutional_authority_statement')->nullable(); // Constitutional basis
            $table->json('committees')->nullable(); // Committee assignments
            $table->json('subjects')->nullable(); // Policy subjects
            $table->string('policy_area', 255)->nullable(); // Primary policy area

            // Status tracking
            $table->enum('status', [
                'introduced',
                'referred_to_committee',
                'reported_by_committee',
                'passed_house',
                'passed_senate',
                'passed_both',
                'failed',
                'vetoed',
                'became_law'
            ])->default('introduced');

            // Metadata
            $table->date('introduced_date');
            $table->timestamp('last_action_at')->nullable();
            $table->string('last_action_text', 500)->nullable();

            // Geographic relevance
            $table->json('affected_states')->nullable(); // Array of state codes
            $table->json('affected_districts')->nullable(); // Array of districts
            $table->boolean('is_national')->default(true);

            // External references
            $table->string('congress_gov_url', 500)->nullable();
            $table->string('govtrack_url', 500)->nullable();

            // Data freshness tracking
            $table->timestamp('last_synced_at')->nullable();
            $table->enum('sync_source', ['api', 'scrape'])->default('api');
            $table->unsignedTinyInteger('confidence_score')->default(100); // 0-100

            $table->timestamps();

            // Indexes for performance
            $table->unique(['congress_number', 'chamber', 'bill_type', 'bill_number']);
            $table->index('status');
            $table->index('introduced_date');
            $table->index('last_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
