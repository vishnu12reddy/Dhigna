<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlistGuestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('glist_guest', function (Blueprint $table) {
            
            $table->bigInteger('glist_id')->unsigned()->index();

            $table->bigInteger('guest_id')->unsigned()->index();

            $table->foreign('glist_id')->references('id')->on('glists')->onDelete('cascade');

            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('glist_guest', function (Blueprint $table) {
            $table->dropForeign('glist_id');
            $table->dropForeign('guest_id');
        });

        Schema::dropIfExists('glist_guest');
    }
}
