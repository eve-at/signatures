<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWormholes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wormholes', function (Blueprint $table) {
            $table->bigIncrements('wormholeId')->unsigned();
            $table->string('wormholeName', 4);
            $table->enum('class', ['HS', 'LS', 'NS', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C13', 'Thera'])->nullable();
            $table->string('ClassName', 10);
            $table->integer('maxStableTime')->comment('hours');
            $table->integer('maxStableMass')->comment('millions kg');
            $table->integer('maxMassRegeneration')->comment('millions kg');
            $table->integer('maxJumpMass')->comment('millions kg');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wormholes');
    }
}
