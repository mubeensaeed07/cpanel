<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get the first admin (role_id = 2) who is approved
        $admin = User::where('role_id', 2)->where('is_approved', true)->first();
        
        if ($admin) {
            // Assign all departments without admin_id to the first admin
            DB::table('departments')
                ->whereNull('admin_id')
                ->update(['admin_id' => $admin->id]);
            
            echo "Assigned existing departments to admin: {$admin->first_name} {$admin->last_name}\n";
        } else {
            echo "No approved admin found. Please create an admin first.\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set admin_id to null for all departments
        DB::table('departments')->update(['admin_id' => null]);
    }
};