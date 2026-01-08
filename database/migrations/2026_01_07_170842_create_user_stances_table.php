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
        Schema::create('user_stances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bill_id')->constrained()->onDelete('cascade');

            // Stance type: support, oppose, mixed, undecided, needs_more_info
            $table->enum('stance', ['support', 'oppose', 'mixed', 'undecided', 'needs_more_info']);

            // Required substantive reason
            $table->text('reason');

            // ZIP code snapshot at time of stance submission
            $table->string('zip_code', 10);
            $table->string('congressional_district', 10)->nullable();

            // Revision tracking
            $table->integer('revision')->default(1);
            $table->foreignId('previous_stance_id')->nullable()->constrained('user_stances')->onDelete('set null');

            // Bill version snapshot
            $table->foreignId('bill_version_id')->nullable()->constrained()->onDelete('set null');

            $table->timestamps();
            $table->softDeletes(); // Preserve stance history

            // User can only have one active stance per bill
            $table->unique(['user_id', 'bill_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stances');
    }
};
