<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('org_description')->nullable();
            $table->string('org_facebook')->nullable();
            $table->string('org_instagram')->nullable();
            $table->string('org_youtube')->nullable();
            $table->string('org_twitter')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['org_description', 'org_facebook', 'org_instagram', 'org_youtube', 'org_twitter']);
        });
    }
}
