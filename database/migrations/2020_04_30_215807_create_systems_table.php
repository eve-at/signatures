<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('systems', function (Blueprint $table) {
            $table->unsignedBigInteger('regionID');
            $table->bigInteger('constellationID');
            $table->bigIncrements('solarSystemID')->unsigned();
            $table->string('solarSystemName', 20); // "Tash-Murkon Prime" is longest (17)
            $table->double('x');
            $table->double('y');
            $table->double('z');
            $table->double('xMin');
            $table->double('xMax');
            $table->double('yMin');
            $table->double('yMax');
            $table->double('zMin');
            $table->double('zMax');
            $table->decimal('luminosity', 12, 10);
            $table->boolean('border');
            $table->boolean('fringe');
            $table->boolean('corridor');
            $table->boolean('hub');
            $table->boolean('international');
            $table->boolean('regional');
            $table->integer('constellation')->nullable();
            $table->decimal('security', 12, 10);
            $table->integer('factionID')->nullable();
            $table->double('radius')->nullable();
            $table->integer('sunTypeID')->nullable();
            $table->string('securityClass', 2)->nullable();

            $table->foreign('regionID')->references('regionID')->on('regions');
            $table->foreign('constellationID')->references('constellationID')->on('constellations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('systems');
    }
}
