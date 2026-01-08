<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Try to create indexes, skip if they already exist (MySQL doesn't support IF NOT EXISTS for indexes)
        $indexes = [
            // Bills table indexes for common query patterns
            ['table' => 'bills', 'columns' => ['last_action_at'], 'name' => 'bills_last_action_at_index'],
            ['table' => 'bills', 'columns' => ['status', 'last_action_at'], 'name' => 'bills_status_last_action_at_index'],
            ['table' => 'bills', 'columns' => ['chamber', 'last_action_at'], 'name' => 'bills_chamber_last_action_at_index'],

            // User stances for consensus calculations
            ['table' => 'user_stances', 'columns' => ['stance'], 'name' => 'user_stances_stance_index'],
            ['table' => 'user_stances', 'columns' => ['bill_id', 'stance'], 'name' => 'user_stances_bill_id_stance_index'],

            // Bill actors for sponsor and representative lookups
            ['table' => 'bill_actors', 'columns' => ['bill_id', 'actor_type', 'is_primary'], 'name' => 'bill_actors_bill_id_actor_type_is_primary_index'],
            ['table' => 'bill_actors', 'columns' => ['actor_type', 'state'], 'name' => 'bill_actors_actor_type_state_index'],

            // Bill events for timeline queries
            ['table' => 'bill_events', 'columns' => ['bill_id', 'occurred_at'], 'name' => 'bill_events_bill_id_occurred_at_index'],

            // Comments for threaded discussions
            ['table' => 'comments', 'columns' => ['discussion_id', 'parent_id', 'created_at'], 'name' => 'comments_discussion_id_parent_id_created_at_index'],

            // Bill followers for recent follows
            ['table' => 'bill_followers', 'columns' => ['followed_at'], 'name' => 'bill_followers_followed_at_index'],
        ];

        foreach ($indexes as $index) {
            try {
                Schema::table($index['table'], function (Blueprint $table) use ($index) {
                    $table->index($index['columns'], $index['name']);
                });
            } catch (\Exception $e) {
                // Index already exists, skip
                if (!str_contains($e->getMessage(), 'Duplicate key name')) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex('bills_last_action_at_index');
            $table->dropIndex('bills_status_last_action_at_index');
            $table->dropIndex('bills_chamber_last_action_at_index');
        });

        Schema::table('user_stances', function (Blueprint $table) {
            $table->dropIndex('user_stances_stance_index');
            $table->dropIndex('user_stances_bill_id_stance_index');
        });

        Schema::table('bill_actors', function (Blueprint $table) {
            $table->dropIndex('bill_actors_bill_id_actor_type_is_primary_index');
            $table->dropIndex('bill_actors_actor_type_state_index');
        });

        Schema::table('bill_events', function (Blueprint $table) {
            $table->dropIndex('bill_events_bill_id_occurred_at_index');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_discussion_id_parent_id_created_at_index');
        });

        Schema::table('bill_followers', function (Blueprint $table) {
            $table->dropIndex('bill_followers_followed_at_index');
        });
    }
};
