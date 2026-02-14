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
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');              // English title (e.g. "Latina Sex Cams")
            $table->string('slug')->unique();     // URL-friendly slug (e.g. "latina")
            $table->string('type')->default('tag_category'); // Section type for future extensibility
            $table->json('tags')->nullable();      // Array of tags to match cam models against
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('max_models')->default(8);   // Max models to display
            $table->integer('min_models')->default(4);   // Min models needed to show section
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });

        Schema::create('homepage_section_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homepage_section_id')->constrained()->onDelete('cascade');
            $table->string('locale', 10);
            $table->string('title');              // Translated title
            $table->text('meta_description')->nullable(); // Optional SEO description
            $table->timestamps();

            $table->unique(['homepage_section_id', 'locale']);
            $table->index('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_section_translations');
        Schema::dropIfExists('homepage_sections');
    }
};
