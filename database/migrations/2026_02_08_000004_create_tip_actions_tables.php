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
        // Global action types (predefined actions that can be assigned to models)
        Schema::create('tip_action_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Flash", "Dance", "Oil Show"
            $table->string('slug')->unique();
            $table->string('emoji')->nullable(); // e.g., "💝", "💃", "🔥"
            $table->string('category')->default('general'); // general, dance, tease, special
            $table->integer('suggested_min_tokens')->default(10);
            $table->integer('suggested_max_tokens')->default(100);
            $table->text('description')->nullable(); // Description of what this action is
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Translations for action types
        Schema::create('tip_action_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tip_action_type_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name'); // Translated name
            $table->text('description')->nullable(); // Translated description
            $table->timestamps();

            $table->unique(['tip_action_type_id', 'locale']);
        });

        // Model-specific tip menus (which actions a model offers and at what price)
        Schema::create('model_tip_menus', function (Blueprint $table) {
            $table->id();
            $table->string('model_id'); // Reference to cam model (from external DB)
            $table->foreignId('tip_action_type_id')->constrained()->cascadeOnDelete();
            $table->integer('token_price'); // Model's custom price for this action
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['model_id', 'tip_action_type_id']);
            $table->index('model_id');
        });

        // Model descriptions (AI generated or manual)
        Schema::create('model_descriptions', function (Blueprint $table) {
            $table->id();
            $table->string('model_id')->unique(); // Reference to cam model
            $table->string('locale', 10)->default('en');
            $table->text('short_description')->nullable(); // 1-2 sentences
            $table->text('long_description')->nullable(); // Full bio
            $table->text('personality_traits')->nullable(); // JSON array of traits
            $table->text('specialties')->nullable(); // What she's known for
            $table->enum('source', ['ai', 'manual', 'imported'])->default('ai');
            $table->boolean('is_approved')->default(false); // Manual approval flag
            $table->timestamps();

            $table->index(['model_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_descriptions');
        Schema::dropIfExists('model_tip_menus');
        Schema::dropIfExists('tip_action_translations');
        Schema::dropIfExists('tip_action_types');
    }
};
