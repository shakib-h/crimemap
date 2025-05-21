<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // First create a default role
        Schema::table('roles', function (Blueprint $table) {
            Role::create([
                'name' => 'User',
                'slug' => 'user'
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            // Add column as nullable first
            $table->foreignId('role_id')->after('id')->nullable();
        });

        // Set default role for existing users
        DB::table('users')->update(['role_id' => Role::where('slug', 'user')->first()->id]);

        Schema::table('users', function (Blueprint $table) {
            // Now make it non-nullable and add the foreign key constraint
            $table->foreignId('role_id')->nullable(false)->change();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
