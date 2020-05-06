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

            // For wormholes
            $table->string('exitCode', 7)->nullable(); // for Wormholes
            $table->unsignedBigInteger('enterSystem');
            $table->unsignedBigInteger('exitSystem')->nullable(); // for Wormholes
            $table->unsignedInteger('enterAnomaly')->nullable(); // for Cosmic Anomalies
            $table->unsignedInteger('exitAnomaly')->nullable(); // for Cosmic Anomalies
            $table->enum('anomalySize', ['V', 'L', 'M', 'S'])->nullable();
            $table->enum('anomalyMass', ['not yet (over 50%)', 'not critical (between 50% and 10%)', 'critical (less than 10%)'])->nullable();
            $table->enum('anomalyTime', ['not yet (24h+)', 'beginning to decay (4h-24h)', 'reaching the end (<4h)'])->nullable();
            $table->enum('anomalyClass', ['Hisec', 'Lowsec', 'Nullsec', 'Deadly, C6', 'Dangerous, C4-C5', 'Unknown, C1-C3', 'Thera'])->nullable();
            $table->datetime('expires_at');


            $table->enum('signatureGroup', ['Cosmic Anomaly', 'Cosmic Signature']);
            $table->enum('anomalyGroup', ['Combat Site', 'Ore Site', 'Gas Site', 'Data Site', 'Relic Site', 'Wormhole'])->nullable(); // for Cosmic Anomalies

            $table->unsignedBigInteger('characterId');
            $table->integer('rating')->default(0);
            $table->timestamps();

            $table->foreign('enterSystem')->references('systemId')->on('systems');
            $table->foreign('exitSystem')->references('systemId')->on('systems');
            $table->foreign('enterAnomaly')->references('anomalyId')->on('anomalies');
            $table->foreign('exitAnomaly')->references('anomalyId')->on('anomalies');
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
