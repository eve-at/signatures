<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    protected $primaryKey = 'solarSystemID';
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
        return in_array($this->class, ['C1','C2','C3','C4','C5','C6','C13','Thera']);
    }

    public function whClass()
    {
        return $this->class . ($this->className ? ' ' . $this->className : '');
    }

    public function toInfoString()
    {
        if ($this->isWH()) {
            return $this->solarSystemName . ", " . $this->class;
        }
        return $this->solarSystemName . " " . $this->security() . " (" . $this->regionName() . ")";
    }

    public function __toString()
    {
        return $this->systemName();
    }
}
