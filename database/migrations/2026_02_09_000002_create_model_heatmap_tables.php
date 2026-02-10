<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create tables for model online heatmap tracking.
     * 
     * Data flow:
     * 1. Hourly cron records online models -> model_online_snapshots
     * 2. Daily aggregation calculates patterns -> model_heatmaps
     * 3. Frontend displays 7x24 heatmap grid
     */
    public function up(): void
    {
        // Raw hourly snapshots - stores which models were online at each hour
        // Keep for 4-8 weeks, then prune old data
        Schema::create('model_online_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('model_id', 100)->index();
            $table->timestamp('snapshot_at'); // When the snapshot was taken
            $table->tinyInteger('day_of_week'); // 0-6 (Sunday-Saturday)
            $table->tinyInteger('hour_of_day'); // 0-23
            $table->boolean('is_online')->default(true);
            $table->integer('viewers_count')->nullable(); // Optional: track viewer count
            $table->string('stream_status', 20)->nullable(); // public, private, away, etc.
            
            // Composite index for efficient querying
            $table->index(['model_id', 'day_of_week', 'hour_of_day']);
            $table->index(['snapshot_at']);
        });

        // Aggregated heatmap data - one row per model per time slot
        // 7 days x 24 hours = 168 potential rows per model
        Schema::create('model_heatmaps', function (Blueprint $table) {
            $table->id();
            $table->string('model_id', 100);
            $table->tinyInteger('day_of_week'); // 0-6 (Sunday-Saturday)
            $table->tinyInteger('hour_of_day'); // 0-23
            $table->integer('times_online')->default(0); // How many times seen online
            $table->integer('times_checked')->default(0); // Total checks for this slot
            $table->decimal('online_percentage', 5, 2)->default(0); // Calculated: times_online/times_checked * 100
            $table->integer('avg_viewers')->nullable(); // Average viewer count when online
            $table->timestamp('last_seen_at')->nullable(); // Last time seen online at this slot
            $table->timestamps();
            
            $table->unique(['model_id', 'day_of_week', 'hour_of_day']);
            $table->index('model_id');
            $table->index(['day_of_week', 'hour_of_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_heatmaps');
        Schema::dropIfExists('model_online_snapshots');
    }
};
