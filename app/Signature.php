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

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'signatureId');
    }

    public function character()
    {
        return Character::find($this->characterId);
    }

    public function exitSystem()
    {
        return $this->exitSystem ? System::find($this->exitSystem) : null;
    }

    public function enterAnomaly()
    {
        return $this->enterAnomaly ? Wormhole::find($this->enterAnomaly) : null;
    }

    public function exitAnomaly()
    {
        return $this->exitAnomaly ? Wormhole::find($this->exitAnomaly) : null;
    }
}
