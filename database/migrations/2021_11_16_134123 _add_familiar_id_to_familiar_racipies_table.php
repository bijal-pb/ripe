<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFamiliarIdToFamiliarRacipiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('familiar_racipies', function (Blueprint $table) {
            $table->unsignedBigInteger('familiar_id')->after('id')->nullable();
            $table->foreign('familiar_id')->references('id')->on('familiars')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('familiar_racipies', function (Blueprint $table) {
            $table->dropColumn('familiar_id');
        });
    }
}
