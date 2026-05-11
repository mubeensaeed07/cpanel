<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow the same slug across different admins (per-admin isolation).
     */
    public function up(): void
    {
        foreach ($this->tables() as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropUnique(['slug']);
                $blueprint->unique(['created_by', 'slug']);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables() as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropUnique(['created_by', 'slug']);
                $blueprint->unique('slug');
            });
        }
    }

    /**
     * @return list<string>
     */
    private function tables(): array
    {
        return [
            'movies',
            'series',
            'web_tvs',
            'wallpapers',
            'games',
            'software',
            'applications',
        ];
    }
};
