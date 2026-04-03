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
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('lead_no')->nullable()->after('id');
            $table->softDeletes();
        });

        Schema::table('lead_users', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('lead_sources', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('lead_tags', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('lead_statuses', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('lead_followups', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('lead_histories', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('lead_step_details', function (Blueprint $table) { $table->softDeletes(); });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['lead_no', 'deleted_at']);
        });
        
        Schema::table('lead_users', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('lead_sources', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('lead_tags', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('lead_statuses', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('lead_followups', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('lead_histories', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('lead_step_details', function (Blueprint $table) { $table->dropSoftDeletes(); });
    }
};
