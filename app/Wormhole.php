<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wormhole extends Model
{
    protected $primaryKey = 'wormholeId';
    public $timestamps = false;

    public function wormholeSize()
    {
        if ($this->maxStableMass == 0) {
            return ""; // K162
        }

        if ($this->maxStableMass > 1000) {
            return "V";
        }

        if ($this->maxJumpMass >= 300) {
            return "L";
        }

        if ($this->maxJumpMass >= 20) {
            return "M";
        }

        return "S";
    }

    public function wormholeClass($short = false)
    {
        if ($short) {
            return str_replace("W-space", "", ucfirst($this->systemType) . str_replace("Class ", "C", $this->systemTypeClass));
        }

        $classes = [
            'hi-sec_' => 'Hisec',
            'low-sec_' => 'Lowsec',
            'null-sec_' => 'Nullsec',
            'w-space_Class 6' => 'Deadly, C6',
            'w-space_Class 5' => 'Dangerous, C4-C5',
            'w-space_Class 4' => 'Dangerous, C4-C5',
            'w-space_Class 3' => 'Unknown, C1-C3',
            'w-space_Class 2' => 'Unknown, C1-C3',
            'w-space_Class 1' => 'Unknown, C1-C3',
            'w-space_Thera' => 'Thera',
        ];
        $key = $this->systemType . '_' . $this->systemTypeClass;

        return $classes[$key] ?? '';
    }

    public function __toString()
    {
        return $this->wormholeName;
    }
}
