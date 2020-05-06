<?php

namespace App\Http\Controllers;

use App\System;
use App\Wormhole;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    public function wormholes(Request $request)
    {
        $wormholes = Wormhole::where('wormholeName', 'LIKE', '%' . $request->term . '%')->orderBy('wormholeName', 'ASC')->get();

        return collect($wormholes)->map(function ($wormhole) {
            $info = [ucfirst($wormhole->systemType), ucfirst($wormhole->systemTypeClass)];
            return [
                'id' => $wormhole->wormholeId,
                'label' => $wormhole->wormholeName . ' (' . implode(', ', $info) . ')',
                'value' => $wormhole->wormholeName,
            ];
        })->toArray();
    }

    public function systems(Request $request)
    {
        $systems = System::where('solarSystemName', 'LIKE', '%' . $request->term . '%')->orderBy('solarSystemName', 'ASC')->get();

        return collect($systems)->map(function ($system) {
            return [
                'id' => $system->wormholeId,
                'label' => $system->solarSystemName . ' (' . $system->regionName() . ')',
                'value' => $system->solarSystemName,
            ];
        })->toArray();
    }
}
