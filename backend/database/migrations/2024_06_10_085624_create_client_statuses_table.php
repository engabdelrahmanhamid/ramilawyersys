<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('color')->nullable();
            $table->integer('sort')->nullable();
            $table->unsignedBigInteger('type_id')->comment('type of admin can use this status');
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
        Schema::dropIfExists('user_statuses');
    }
}
