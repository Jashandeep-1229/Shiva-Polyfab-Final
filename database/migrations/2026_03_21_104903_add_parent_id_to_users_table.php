<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'parent_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('created_by_id');
                $table->foreign('parent_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        // Migrate parent_ids from lead_users
        $leadUsers = DB::table('lead_users')->whereNotNull('parent_id')->get();
        foreach ($leadUsers as $lu) {
            $user = DB::table('users')->where('email', $lu->email)->first();
            $parentLu = DB::table('lead_users')->where('id', $lu->parent_id)->first();
            if ($user && $parentLu) {
                $parentUser = DB::table('users')->where('email', $parentLu->email)->first();
                if ($parentUser) {
                    DB::table('users')->where('id', $user->id)->update(['parent_id' => $parentUser->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
