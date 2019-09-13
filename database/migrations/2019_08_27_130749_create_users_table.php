<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(false); // This is for active users
            $table->boolean('is_premium')->default(false); // This is for premium membership
            $table->boolean('is_confirmed')->default(false); // This will be for email confirmation
            $table->boolean('is_ban')->default(false); // This is to ban a user from using the system
            $table->boolean('has_paid')->default(false); // This is to check if a user has paid the one off fee
            $table->boolean('is_bvn_verified')->default(false);
            $table->boolean('profile_updated')->default(false); // This is to check if the user's basic profile is updated
            $table->boolean('work_history_updated')->default(false); // This is to check if the work history for worker is updated
            $table->boolean('skills_updated')->default(false); // This is to check if the skills for worker is updated
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
