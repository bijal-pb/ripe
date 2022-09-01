<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('food_category_id');
            $table->foreign('food_category_id')->references('id')->on('food_categories')->onDelete('cascade');
            $table->unsignedBigInteger('country_id');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->string('title');
            $table->time('preparation_time');
            $table->integer('serves');
            $table->enum('difficulty', ['easy','medium','hard'])->comment('1 - easy | 2 - medium | 3 - hard');
            $table->text('videos');
            $table->longText('ingredients');
            $table->boolean('is_published')->comment('0 - unpublished | 1 - published')->default(1);
            $table->string('thumbnail')->nullable();;
            $table->softDeletes();
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
        Schema::dropIfExists('videos');
    }
}
