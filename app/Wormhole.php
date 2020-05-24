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
            return $this->class;
        }

        $classes = [
            'HS' => 'High-sec',
            'LS' => 'Low-sec',
            'NS' => 'Null-sec',
            'C6' => 'Deadly, C6',
            'C5' => 'Dangerous, C4-C5',
            'C4' => 'Dangerous, C4-C5',
            'C3' => 'Unknown, C1-C3',
            'C2' => 'Unknown, C1-C3',
            'C1' => 'Unknown, C1-C3',
            'Thera' => 'Thera',
        ];
        return $classes[$this->class] ?? '';
    }

    public function __toString()
    {
        return $this->wormholeName;
    }

    public function toInfoString()
    {
        return $this->wormholeName . ", " . $this->class;
    }

    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [
                'wormholeSize' => $this->wormholeSize(),
                'wormholeClass' => $this->wormholeClass(),
                'wormholeClassShort' => $this->wormholeClass(true),
            ]
        );
    }
}
