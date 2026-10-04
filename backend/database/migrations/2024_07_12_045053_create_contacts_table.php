<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('date')->nullable();
            $table->tinyInteger('method')->default(1)->comment('1=>from us ,2=> from client' )->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('contact_reason_id')->nullable();
            $table->foreign('contact_reason_id')->references('id')->on('contact_reasons')->onDelete('cascade');
            $table->unsignedBigInteger('contact_type_id')->nullable();
            $table->foreign('contact_type_id')->references('id')->on('types')->onDelete('cascade');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->unsignedBigInteger('createdBy_id')->nullable();
            $table->foreign('createdBy_id')->references('id')->on('admins')->onDelete('cascade');
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
        Schema::dropIfExists('contacts');
    }
}
