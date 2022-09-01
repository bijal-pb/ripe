<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('photo')->nullable();
            $table->longText('description')->nullable();
            $table->tinyInteger('type')->comment('1-cook | 2 - learn')->nullable();
            $table->string('social_type')->comment('google | mac | facebook')->nullable();
            $table->string('social_id')->nullable();
            $table->boolean('is_notification')->comment('1 - enable | 2 - disable')->default(1);
            $table->boolean('status')->comment('1 - active | 2 - deactive')->default(1);
            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id')->references('id')->on('countries');
            $table->string('device_type')->nullable();
            $table->text('device_token')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
