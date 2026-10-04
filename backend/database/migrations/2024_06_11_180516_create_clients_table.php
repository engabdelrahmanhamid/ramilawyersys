<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('secondary_phone')->nullable();
            $table->string('image')->nullable();
            $table->text('id_number')->nullable();
            $table->text('tax_number')->nullable();
            $table->text('commercial_register')->nullable();
            $table->text('address')->nullable();
            $table->text('case_type')->nullable();
            $table->string('status_updated_at')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
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
        Schema::dropIfExists('clients');
    }
}
