<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->bigIncrements('regionID')->unsigned();
            $table->string('regionName');
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
            $table->enum('class', ['HS', 'LS', 'NS', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C13', 'Thera'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regions');
    }
}
