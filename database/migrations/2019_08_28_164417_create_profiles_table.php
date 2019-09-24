<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('first_name');
            $table->string('last_name');
            $table->longText('avatar')->default('data:image/jpeg;base64,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');
            $table->string("gender")->nullable();
            $table->string('bank_verification_number')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->text('bio')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profiles');
    }
}
