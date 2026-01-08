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
        Schema::table('users', function (Blueprint $table) {
            $table->string('zip_code', 10)->nullable()->after('email');
            $table->string('congressional_district', 20)->nullable()->after('zip_code');
            $table->timestamp('zip_code_verified_at')->nullable()->after('congressional_district');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['zip_code', 'congressional_district', 'zip_code_verified_at']);
        });
    }
};
