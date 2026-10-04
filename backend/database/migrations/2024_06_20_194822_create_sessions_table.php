<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type')->nullable()->comment('1 => session , 2=> revision');
            $table->string('link')->nullable();
            $table->date('hijri_date')->nullable();
            $table->date('gregorian_date')->nullable();
            $table->time('session_time')->nullable();
            $table->dateTime('session_reminder_time')->nullable();
            $table->longText('session_requirements')->nullable();
            $table->string('destination')->nullable();
            $table->longText('session_notes')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->foreign('status_id')->references('id')->on('session_statuses')->onDelete('cascade');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->unsignedBigInteger('case_id')->nullable();
            $table->foreign('case_id')->references('id')->on('user_cases')->onDelete('cascade');
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
        Schema::dropIfExists('sessions');
    }
}
