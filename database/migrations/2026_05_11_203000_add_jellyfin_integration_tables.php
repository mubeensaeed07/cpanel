<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jellyfin_connections', function (Blueprint $table): void {
            $table->id();
            $table->string('server_url');
            $table->text('api_key');
            $table->string('user_id');
            $table->string('server_name')->nullable();
            $table->timestamps();
        });

        Schema::table('admins', function (Blueprint $table): void {
            $table->boolean('connect_jellyfin')->default(false)->after('connect_g_drive');
            $table->timestamp('jellyfin_last_synced_at')->nullable()->after('connect_jellyfin');
            $table->string('jellyfin_sync_status', 30)->nullable()->after('jellyfin_last_synced_at');
            $table->text('jellyfin_last_error')->nullable()->after('jellyfin_sync_status');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->dropColumn([
                'connect_jellyfin',
                'jellyfin_last_synced_at',
                'jellyfin_sync_status',
                'jellyfin_last_error',
            ]);
        });

        Schema::dropIfExists('jellyfin_connections');
    }
};
