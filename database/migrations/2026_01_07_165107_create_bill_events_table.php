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
        Schema::create('bill_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();

            // Event details
            $table->enum('event_type', [
                'introduced',
                'referred_to_committee',
                'committee_action',
                'reported_by_committee',
                'floor_action',
                'vote',
                'passed_chamber',
                'failed_vote',
                'sent_to_other_chamber',
                'amended',
                'conference_committee',
                'sent_to_president',
                'signed_by_president',
                'vetoed',
                'became_law',
                'other'
            ]);

            $table->text('description');
            $table->timestamp('occurred_at');

            // Context
            $table->string('chamber', 20)->nullable(); // house, senate, executive
            $table->string('committee_name', 200)->nullable();
            $table->json('vote_details')->nullable(); // yeas, nays, present, etc.

            // Source tracking (immutable event log)
            $table->enum('source', ['api', 'scrape'])->default('api');
            $table->timestamp('detected_at')->useCurrent();
            $table->json('raw_payload')->nullable(); // Store original API/scrape data

            $table->timestamps();

            // Indexes
            $table->index(['bill_id', 'occurred_at']);
            $table->index('event_type');
            $table->index('detected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_events');
    }
};
