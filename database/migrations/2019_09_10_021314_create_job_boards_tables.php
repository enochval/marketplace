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
            $table->integer('worker_id')->nullable()->unsigned();
            $table->string('title');
            $table->text('description');
            $table->string('duration')->default(0);
            $table->string('frequency')->default(1);
            $table->string('originating_amount')->default(0);
            $table->string('terminating_amount')->default(0);
            $table->string('images')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(false);
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
