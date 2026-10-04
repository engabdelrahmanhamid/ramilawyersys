<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('model_type')->nullable();
            $table->string('model_id')->nullable();
            $table->string('end_date')->nullable();
            $table->string('description')->nullable();
            $table->string('status')->default('1')->comment('1=>new , 2=>ended , 3=>ended after date');
            $table->string('comment')->nullable();
            $table->unsignedBigInteger('task_type_id')->nullable();
            $table->foreign('task_type_id')->references('id')->on('types')->onDelete('cascade');
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->foreign('created_by_id')->references('id')->on('admins')->onDelete('cascade');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
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
        Schema::dropIfExists('tasks');
    }
}
