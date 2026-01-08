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
        Schema::create('bill_actors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();

            // Actor identification
            $table->enum('actor_type', ['sponsor', 'cosponsor', 'committee', 'agency']);

            // Person details (for sponsors/cosponsors)
            $table->string('bioguide_id', 20)->nullable(); // Congress.gov identifier
            $table->string('name', 200);
            $table->string('party', 50)->nullable(); // D, R, I, etc.
            $table->string('state', 2)->nullable(); // State code
            $table->string('district', 10)->nullable(); // For House members

            // Committee details
            $table->string('committee_code', 20)->nullable();
            $table->string('committee_name', 200)->nullable();

            // Metadata
            $table->timestamp('joined_at')->nullable(); // When they became associated
            $table->boolean('is_primary')->default(false); // Primary sponsor
            $table->boolean('is_original')->default(false); // Original cosponsor

            $table->timestamps();

            // Indexes
            $table->index(['bill_id', 'actor_type']);
            $table->index('bioguide_id');
            $table->index(['state', 'district']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_actors');
    }
};
