<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_files', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('date')->nullable();
            $table->string('file')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
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
        Schema::dropIfExists('client_files');
    }
}
