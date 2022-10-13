<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->decimal('rating',2,1);
            $table->text('review')->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->integer('event_id')->unsigned();
            $table->boolean('status')->default('0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            
            $table->dropForeign('user_id');
            $table->dropForeign('event_id');
        });
        Schema::dropIfExists('reviews');
    }
}
