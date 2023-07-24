<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('candidates', function (Blueprint $table) {
          $table->bigIncrements('id');
          $table->unsignedBigInteger('user_id')->comment('id of users');
          $table->string('first_name', 40)->nullable();
          $table->string('last_name', 40)->nullable();
          $table->string('email', 40)->nullable();
          $table->string('contact_no', 100)->nullable();
          $table->tinyInteger('gender')->default(NULL)->comment('1 - Male, 2-> Female');
          $table->string('specialization', 200)->nullable();
          $table->string('address', 500)->nullable();
          $table->string('skill', 200)->nullable();
          $table->string('resume', 255)->nullable();
          $table->integer('work_ex_year')->nullable();
          $table->timestamp('candidate_dob', $precision = 0)->nullable();
          $table->timestamps($precision = 0);
          $table->softDeletes();
          $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('candidates');
    }
};
