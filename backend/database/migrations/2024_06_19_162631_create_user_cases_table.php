<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_cases', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('number')->nullable();
            $table->date('hijri_date')->nullable();
            $table->date('gregorian_date')->nullable();
            $table->string('desc')->nullable();
            $table->string('court_name')->nullable();
            $table->string('court_city')->nullable();
            $table->string('court_circle')->nullable();
            $table->string('court_degree')->nullable();
            $table->string('opponent_name')->nullable();
            $table->double('amount',10,2)->nullable();
            $table->double('deposit',10,2)->nullable();
            $table->tinyInteger('payment_status')->default(0)->comment('0=>paid ,1=>unpaid')->nullable();
            $table->tinyInteger('client_characteristic')->comment('1=>claimant , 2=>Defendant')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->unsignedBigInteger('opponent_type_id')->nullable();
            $table->foreign('opponent_type_id')->references('id')->on('types')->onDelete('cascade');
            $table->unsignedBigInteger('case_type_id')->nullable();
            $table->foreign('case_type_id')->references('id')->on('types')->onDelete('cascade');
            $table->unsignedBigInteger('case_status_id')->nullable();
            $table->foreign('case_status_id')->references('id')->on('case_statuses')->onDelete('cascade');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
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
        Schema::dropIfExists('user_cases');
    }
}
