<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table): void {
            $table->string('imdb_id', 20)->nullable()->after('slug');
            $table->text('poster_url')->nullable()->after('description');
            $table->string('release_year', 20)->nullable()->after('poster_url');
            $table->string('imdb_rating', 20)->nullable()->after('release_year');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table): void {
            $table->dropColumn(['imdb_id', 'poster_url', 'release_year', 'imdb_rating']);
        });
    }
};
