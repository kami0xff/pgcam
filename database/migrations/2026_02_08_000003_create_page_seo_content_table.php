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
        // Page-level SEO content (for homepage, index pages, etc.)
        Schema::create('page_seo_content', function (Blueprint $table) {
            $table->id();
            $table->string('page_key'); // 'home', 'tags_index', 'countries_index', etc.
            $table->string('locale', 10)->default('en');
            $table->string('title')->nullable(); // Section title
            $table->text('content'); // Main SEO text
            $table->text('keywords')->nullable(); // Target keywords (comma separated)
            $table->enum('position', ['top', 'bottom'])->default('bottom');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['page_key', 'locale']);
            $table->index('page_key');
            $table->index('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_seo_content');
    }
};
