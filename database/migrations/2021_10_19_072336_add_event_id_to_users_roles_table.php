<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEventIdToUsersRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('user_roles', function (Blueprint $table) {
            // Delete old relation
            $table->dropPrimary(['user_id','role_id']); 
            
            // add new relation
            $table->integer('event_id')->nullable();
            $table->unique(['user_id','role_id', 'event_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropForeign('event_id'); 
            $table->dropColumn('event_id');
            
            $table->dropForeign('user_id'); 
            $table->dropColumn('user_id');
            
            $table->dropForeign('role_id'); 
            $table->dropColumn('role_id');
        });
    }
}
