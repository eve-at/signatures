<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->bigIncrements('ratingId');
            $table->unsignedBigInteger('signatureId');
            $table->unsignedBigInteger('characterId');
            $table->string('characterName');
            $table->boolean('liked')->default(0);
            $table->timestamps();

            $table->foreign('signatureId')->references('signatureId')->on('signatures')->onDelete('cascade');
            $table->foreign('characterId')->references('characterId')->on('characters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ratings');
    }
}
