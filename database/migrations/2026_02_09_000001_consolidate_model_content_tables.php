<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Consolidate model content tables:
     * - Remove redundant model_seo_content (merged into model_descriptions)
     * - Add model_description_translations for multilingual descriptions
     * - Add model_faq_translations for multilingual FAQs  
     * - Simplify tip menus (remove complex tip_action_types)
     * - Add model_goals for goal history tracking
     * - Add model_schedules for planner/heatmap data
     * - Add page_seo_contents for flexible page SEO (if not exists)
     */
    public function up(): void
    {
        // 1. Refactor model_descriptions to be base English content only
        // Change unique constraint from model_id to composite
        if (Schema::hasColumn('model_descriptions', 'locale')) {
            Schema::table('model_descriptions', function (Blueprint $table) {
                // Keep only English content in base table
                $table->dropIndex(['model_id', 'locale']);
            });
            
            // Remove locale column - translations will be in separate table
            Schema::table('model_descriptions', function (Blueprint $table) {
                $table->dropColumn('locale');
            });
        }

        // 2. Create model_description_translations
        Schema::create('model_description_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_description_id')->constrained('model_descriptions')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->text('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->text('specialties')->nullable();
            $table->timestamps();

            $table->unique(['model_description_id', 'locale']);
            $table->index('locale');
        });

        // 3. Refactor model_faqs - change to group FAQs per model
        // First migrate existing data structure
        Schema::table('model_faqs', function (Blueprint $table) {
            // Rename cam_model_id to model_id for consistency
            if (Schema::hasColumn('model_faqs', 'cam_model_id')) {
                $table->renameColumn('cam_model_id', 'model_id');
            }
        });

        // 4. Create model_faq_translations
        Schema::create('model_faq_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_faq_id')->constrained('model_faqs')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('question');
            $table->text('answer');
            $table->timestamps();

            $table->unique(['model_faq_id', 'locale']);
            $table->index('locale');
        });

        // 5. Simplify tip menus - store raw tip menu items directly
        Schema::create('model_tip_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('model_id'); // Reference to cam model
            $table->string('action_name'); // Raw action name from source (e.g., "Flash", "Dance")
            $table->integer('token_price');
            $table->string('emoji')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('model_id');
            $table->unique(['model_id', 'action_name']);
        });

        // 6. Create tip menu item translations
        Schema::create('model_tip_menu_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_tip_menu_item_id')->constrained('model_tip_menu_items')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('action_name'); // Translated action name
            $table->timestamps();

            $table->unique(['model_tip_menu_item_id', 'locale']);
        });

        // 7. Create model_goals for tracking goal history
        Schema::create('model_goals', function (Blueprint $table) {
            $table->id();
            $table->string('model_id');
            $table->text('goal_message'); // Raw goal text from model
            $table->integer('tokens_needed')->nullable();
            $table->integer('tokens_earned')->nullable();
            $table->boolean('was_completed')->default(false);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('model_id');
            $table->index('started_at');
        });

        // 8. Create model_schedules for planner/heatmap data
        Schema::create('model_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('model_id');
            $table->tinyInteger('day_of_week'); // 0-6 (Sunday-Saturday)
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_usually_online')->default(false);
            $table->integer('confidence_score')->default(0); // 0-100
            $table->integer('sample_count')->default(0); // How many times we've seen this pattern
            $table->timestamps();

            $table->unique(['model_id', 'day_of_week']);
            $table->index('model_id');
        });

        // 9. Create page_seo_contents if not exists (flexible page SEO)
        if (!Schema::hasTable('page_seo_contents')) {
            Schema::create('page_seo_contents', function (Blueprint $table) {
                $table->id();
                $table->string('page_key'); // home, niche_girls, tag_young, country_us
                $table->string('locale', 10)->default('en');
                $table->string('title')->nullable();
                $table->text('content')->nullable();
                $table->string('keywords')->nullable();
                $table->string('position')->default('bottom'); // top, bottom
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['page_key', 'locale']);
                $table->index('locale');
                $table->index('page_key');
            });
        }

        // 10. Drop redundant tables
        Schema::dropIfExists('model_seo_content');
        
        // Keep tip_action_types and tip_action_translations for now (can migrate data later)
        // But mark as deprecated in comments
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_schedules');
        Schema::dropIfExists('model_goals');
        Schema::dropIfExists('model_tip_menu_translations');
        Schema::dropIfExists('model_tip_menu_items');
        Schema::dropIfExists('model_faq_translations');
        Schema::dropIfExists('model_description_translations');
        
        // Re-add locale to model_descriptions
        Schema::table('model_descriptions', function (Blueprint $table) {
            $table->string('locale', 10)->default('en')->after('model_id');
            $table->index(['model_id', 'locale']);
        });
        
        // Restore model_seo_content
        Schema::create('model_seo_content', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cam_model_id');
            $table->string('locale', 10)->default('en');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('long_description')->nullable();
            $table->text('custom_content')->nullable();
            $table->timestamps();
            
            $table->unique(['cam_model_id', 'locale']);
        });
    }
};
