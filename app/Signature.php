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

    public function enterSystem()
    {
        return $this->enterSystem ? System::find($this->enterSystem) : null;
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

        $enterAnomaly = $this->enterAnomaly();
        $exitAnomaly = $this->exitAnomaly();
        $enterSystem = $this->enterSystem();
        $exitSystem = $this->exitSystem();

        $summary = [""];
        if ($enterAnomaly) {
            $summary[0] = $enterAnomaly->wormholeName;
            if ($enterAnomaly->wormholeName === 'K162') {
                $summary[0] .= "->";
                if ($exitAnomaly = $this->exitAnomaly()) {
                    $summary[0] .= $exitAnomaly->wormholeName;

                    $staticData = [];
                    $staticData[] = $exitAnomaly->wormholeClass(true);
                    $staticData[] = $exitAnomaly->wormholeSize();
                } else {
                    $summary[0] .= '?';
                }
            } else {
                $staticData = [];
                $staticData[] = $enterAnomaly->wormholeClass(true);
                $staticData[] = $enterAnomaly->wormholeSize();
            }
        }

        if (count($staticData)) {
            $summary[0] .= " (" . implode(', ', $staticData) . ")";
        }

        if ($this->anomalyMass) {
            $summary[] = 'Mass: ' . $this->anomalyMass;
        }

        if ($this->anomalyTime) {
            $summary[] = 'Time: ' . $this->anomalyTime;
        }

        if ($enterAnomaly && $exitSystem && $enterAnomaly->wormholeName != "K162" && $enterAnomaly->class != $exitSystem->class) {
            $summary[] = "<span class='summaryError'>" . $enterAnomaly->toInfoString() . " cannot leads to " . $exitSystem->toInfoString() . "</span>";
        } else if ($exitAnomaly && $enterSystem && $exitAnomaly->class != $enterSystem->class) {
            $summary[] = "<span class='summaryError'>" . $exitAnomaly->toInfoString() . " cannot leads to " . $enterSystem->toInfoString() . "</span>";
        }

        return implode('<br>', $summary);
    }
}
