<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_seo_content');
    }
};
