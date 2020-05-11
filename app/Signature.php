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

    public function summary()
    {
        $staticData = [];
        if ($this->anomalyClass) {
            $staticData[] = 'to ' . $this->anomalyClass;
        }
        if ($this->anomalySize) {
            $staticData[] = 'Size: ' . $this->anomalySize;
        }

        $summary = [];
        if ($enterAnomaly = $this->enterAnomaly()) {
            $summary[0] = $enterAnomaly->wormholeName;
            if ($enterAnomaly->wormholeName === 'K162') {
                $summary[0] .= "->";
                if ($exitAnomaly = $this->exitAnomaly()) {
                    $summary[0] .= $exitAnomaly->wormholeName;

                    $staticData = [];
                    $staticData[] = 'to ' . $exitAnomaly->wormholeClass(true);
                    $staticData[] = 'Size: ' . $exitAnomaly->wormholeSize();
                } else {
                    $summary[0] .= '?';
                }
            } else {
                $staticData = [];
                $staticData[] = 'to ' . $enterAnomaly->wormholeClass(true);
                $staticData[] = 'Size: ' . $enterAnomaly->wormholeSize();
            }
        }

        if (count($staticData)) {
            $summary[] = implode(', ', $staticData);
        }

        if ($this->anomalyMass) {
            $summary[] = 'Mass: ' . $this->anomalyMass;
        }

        if ($this->anomalyTime) {
            $summary[] = 'Time: ' . $this->anomalyTime;
        }

        return implode(', ', $summary);
    }
}
