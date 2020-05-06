<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wormhole extends Model
{
    protected $primaryKey = 'wormholeId';
    public $timestamps = false;

    public function __toString()
    {
        return $this->wormholeName;
    }
}
