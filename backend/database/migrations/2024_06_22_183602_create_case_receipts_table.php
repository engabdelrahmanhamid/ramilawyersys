<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('case_receipts', function (Blueprint $table) {
            $table->id();
            $table->double('amount',10,2)->nullable();
            $table->tinyInteger('payment_status')->default(0)->comment('0=>paid ,1=>unpaid')->nullable();
            $table->string('date')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();
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
        Schema::dropIfExists('case_receipts');
    }
}
