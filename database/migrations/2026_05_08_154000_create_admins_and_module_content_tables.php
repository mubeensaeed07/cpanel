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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->json('module_permissions');
            $table->timestamps();
        });

        $this->createContentAndLinksTable('movies', 'movie_download_links', 'movie_id');
        $this->createContentAndLinksTable('series', 'series_download_links', 'series_id');
        $this->createContentAndLinksTable('web_tvs', 'web_tv_download_links', 'web_tv_id');
        $this->createContentAndLinksTable('wallpapers', 'wallpaper_download_links', 'wallpaper_id');
        $this->createContentAndLinksTable('games', 'game_download_links', 'game_id');
        $this->createContentAndLinksTable('software', 'software_download_links', 'software_id');
        $this->createContentAndLinksTable('applications', 'application_download_links', 'application_id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_download_links');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('software_download_links');
        Schema::dropIfExists('software');
        Schema::dropIfExists('game_download_links');
        Schema::dropIfExists('games');
        Schema::dropIfExists('wallpaper_download_links');
        Schema::dropIfExists('wallpapers');
        Schema::dropIfExists('web_tv_download_links');
        Schema::dropIfExists('web_tvs');
        Schema::dropIfExists('series_download_links');
        Schema::dropIfExists('series');
        Schema::dropIfExists('movie_download_links');
        Schema::dropIfExists('movies');
        Schema::dropIfExists('admins');
    }

    private function createContentAndLinksTable(string $contentTable, string $linksTable, string $foreignKey): void
    {
        Schema::create($contentTable, function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create($linksTable, function (Blueprint $table) use ($contentTable, $foreignKey) {
            $table->id();
            $table->foreignId($foreignKey)->constrained($contentTable)->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('file_name')->nullable();
            $table->text('file_url');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }
};
