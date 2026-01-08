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
        Schema::create('zip_codes', function (Blueprint $table) {
            $table->string('zip_code', 5)->primary();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('state_code', 2)->index();
            $table->string('congressional_district', 10)->nullable()->index();
            $table->string('city', 100)->nullable();
            $table->string('county', 100)->nullable();
            $table->timestamp('district_updated_at')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index(['state_code', 'congressional_district']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zip_codes');
    }
};
