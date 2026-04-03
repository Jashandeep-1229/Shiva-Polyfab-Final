<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $header) {
            $header->timestamp('last_active_at')->nullable();
        });
    }
 
    public function down()
    {
        Schema::table('users', function (Blueprint $header) {
            $header->dropColumn('last_active_at');
        });
    }
};
