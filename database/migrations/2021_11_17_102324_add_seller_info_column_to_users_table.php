<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSellerInfoColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('seller_name',256);
            $table->text('seller_info',256);
            $table->text('seller_tax_info',256);
            $table->text('seller_signature',256);
            $table->text('seller_note',256);
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
            $table->dropColumn(['seller_name', 'seller_info', 'seller_tax_info', 'seller_signature', 'seller_note']);
        });
    }
}
