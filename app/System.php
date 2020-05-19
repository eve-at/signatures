<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    protected $primaryKey = 'solarSystemId';
    public $timestamps = false;

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

    public function security()
    {
        return number_format(max(0, $this->security), 1, '.', '');
    }

    public function isWH()
    {
        // Exception : J1226-0 (C4)
        return preg_match('/^J\d{4}/', $this->solarSystemName) || $this->solarSystemName === ' Thera';
    }

    public function whClass()
    {
        // TODO: add WH Class to system table
        return "C?";
    }

    public function toInfoString()
    {
        if ($this->isWH()) {
            return $this->solarSystemName . ", " . $this->whClass();
        }
        return $this->solarSystemName . " " . $this->security() . " (" . $this->regionName() . ")";
    }

    public function __toString()
    {
        return $this->systemName();
    }
}
