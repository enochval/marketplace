<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobBoardsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_boards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('employer_id')->unsigned();
            $table->string('title');
            $table->text('description');
            $table->integer('category_id');
            $table->string('duration')->nullable();
            $table->string('frequency')->nullable();
            $table->string('budget')->default('Negotiable');
            $table->string('gender')->default('Any');
            $table->integer('no_of_resource')->default(0);
            $table->integer('hired_count')->default(0);
            $table->string('address')->nullable();
            $table->string('city_id')->nullable();
            $table->string('state')->nullable();
            $table->boolean('is_submitted')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_running')->default(false);
            $table->boolean('is_completed')->default(false);
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
        Schema::dropIfExists('job_boards');
    }
}
