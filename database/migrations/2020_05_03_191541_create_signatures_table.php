<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->bigIncrements('signatureId');
            $table->string('enterCode', 7);
            $table->unsignedBigInteger('enterSystem');
            $table->enum('signatureGroup', ['Cosmic Anomaly', 'Cosmic Signature']);
            $table->enum('anomalyGroup', ['Combat Site', 'Ore Site', 'Gas Site', 'Data Site', 'Relic Site', 'Wormhole'])->nullable(); // for Cosmic Anomalies
            $table->unsignedInteger('anomalyId')->nullable(); // for Cosmic Anomalies
            $table->string('exitCode', 7)->nullable(); // for Wormholes
            $table->unsignedBigInteger('exitSystem')->nullable(); // for Wormholes
            $table->unsignedBigInteger('characterId');
            $table->timestamps();
            $table->datetime('expires_at');

            $table->foreign('enterSystem')->references('systemId')->on('systems');
            $table->foreign('anomalyId')->references('anomalyId')->on('anomalies');
            $table->foreign('exitSystem')->references('systemId')->on('systems');
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
        Schema::dropIfExists('signatures');
    }
}
