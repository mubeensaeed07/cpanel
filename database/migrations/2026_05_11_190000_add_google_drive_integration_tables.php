<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_drive_connections', function (Blueprint $table): void {
            $table->id();
            $table->string('provider')->default('google_drive');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('google_email')->nullable();
            $table->string('google_name')->nullable();
            $table->timestamps();
        });

        Schema::table('admins', function (Blueprint $table): void {
            $table->boolean('connect_g_drive')->default(false)->after('module_permissions');
            $table->timestamp('gdrive_last_synced_at')->nullable()->after('connect_g_drive');
            $table->string('gdrive_sync_status', 30)->nullable()->after('gdrive_last_synced_at');
            $table->text('gdrive_last_error')->nullable()->after('gdrive_sync_status');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->dropColumn([
                'connect_g_drive',
                'gdrive_last_synced_at',
                'gdrive_sync_status',
                'gdrive_last_error',
            ]);
        });

        Schema::dropIfExists('google_drive_connections');
    }
};
