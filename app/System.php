<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    protected $primaryKey = 'solarSystemId';

    public function systemName()
    {
        return $this->solarSystemName;
    }

    public function regionName()
    {
        return Region::find($this->regionID)->regionName;
    }

    public function constellationName()
    {
        return Constellation::find($this->constellationID)->constellationName;
    }
}
