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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('no_resi');
            $table->unsignedBigInteger('partner_id');
            $table->unsignedBigInteger('courier_id');
            $table->string('status_recipient')->nullable();
            $table->string('note')->nullable();
            $table->string('photo_received')->nullable();
            $table->dateTime('date_delivery')->nullable();
            $table->dateTime('date_received')->nullable();
            $table->string('status')->nullable();
            $table->string('status_received')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
};
