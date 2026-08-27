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
        Schema::create('home_pages', function (Blueprint $table) {
            $table->id();

            // Hero
            $table->string('hero_title')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('hero_button_1_text')->nullable();
            $table->string('hero_button_1_link')->nullable();
            $table->string('hero_button_2_text')->nullable();
            $table->string('hero_button_2_link')->nullable();
            $table->string('hero_image_1')->nullable();
            $table->string('hero_image_2')->nullable();
            $table->string('hero_image_3')->nullable();

            // Features
            $table->string('feature_1_icon')->nullable();
            $table->string('feature_1_title')->nullable();
            $table->text('feature_1_description')->nullable();

            $table->string('feature_2_icon')->nullable();
            $table->string('feature_2_title')->nullable();
            $table->text('feature_2_description')->nullable();

            $table->string('feature_3_icon')->nullable();
            $table->string('feature_3_title')->nullable();
            $table->text('feature_3_description')->nullable();

            // Team
            $table->string('team_title')->nullable();
            $table->text('team_description')->nullable();
            $table->string('team_button_text')->nullable();
            $table->string('team_button_link')->nullable();
            $table->string('team_image')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_pages');
    }
};
