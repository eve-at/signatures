<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableConstellations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('constellations', function (Blueprint $table) {
            //regionID,constellationID,constellationName,x,y,z,xMin,xMax,yMin,yMax,zMin,zMax,factionID,radius
            $table->unsignedBigInteger('regionID');
            $table->bigIncrements('constellationID')->unsigned();
            $table->string('constellationName', 50); // "Tash-Murkon Prime" is longest (17)
            $table->double('x');
            $table->double('y');
            $table->double('z');
            $table->double('xMin');
            $table->double('xMax');
            $table->double('yMin');
            $table->double('yMax');
            $table->double('zMin');
            $table->double('zMax');
            $table->integer('factionID')->nullable();
            $table->double('radius')->nullable();

            $table->foreign('regionID')->references('regionID')->on('regions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('constellations');
    }
}
