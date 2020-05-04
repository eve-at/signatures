<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    protected $primaryKey = 'signatureId';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'enterCode',
        'enterSystem',
        'signatureGroup',
        'anomalyGroup',
        'characterId',
        'created_at',
        'expires_at',
    ];

    public function character()
    {
        return Character::find($this->characterId);
    }
}
