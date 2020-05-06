<?php

namespace App\Http\Controllers;

use App\Signature;
use App\System;
use App\Wormhole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AjaxController extends Controller
{
    public function wormholes(Request $request)
    {
        $wormholes = Wormhole::where('wormholeName', 'LIKE', '%' . $request->term . '%')->orderBy('wormholeName', 'ASC')->get();

        return collect($wormholes)->map(function ($wormhole) {
            $info = ucfirst($wormhole->systemType) . ($wormhole->systemTypeClass ? ', ' . ucfirst($wormhole->systemTypeClass) : '');
            return [
                'id' => $wormhole->wormholeId,
                'label' => $wormhole->wormholeName . ' (' . $info . ')',
                'value' => $wormhole->wormholeName,
            ];
        })->toArray();
    }

    public function systems(Request $request)
    {
        $systems = System::where('solarSystemName', 'LIKE', '%' . $request->term . '%')->orderBy('solarSystemName', 'ASC')->get();

        return collect($systems)->map(function ($system) {
            return [
                'id' => $system->solarSystemID,
                'label' => $system->solarSystemName . ' (' . $system->regionName() . ')',
                'value' => $system->solarSystemName,
            ];
        })->toArray();
    }

    public function signature(Request $request)
    {
        if (! isset($request->field) || ! isset($request->value)) {
            return response()->json(['error' => 'Missing fields'], 400);
        }
        $arr = explode('_', $request->field);
        if (count($arr) != 2) {
            return response()->json(['error' => 'Missing fields'], 400);
        }
        list($field, $signatureId) = $arr;

        if (! in_array($field, ['enterAnomaly', 'exitAnomaly', 'exitCode', 'exitSystem'])) {
            return response()->json(['error' => 'Bad values'], 400);
        }

        // 1. Find character's signature
        $arrEveData = Session::get(\Config::get('constants.eve_data_session_variable'));

        $signature = Signature::where([
            ["characterId", "=", $arrEveData['characterId']],
            ["signatureId", "=", $signatureId],
        ])->first();
        if (! $signature) {
            return response()->json(['error' => 'Bad values'], 400);
        }

        // 2.1 Set Wormhole
        if (in_array($field , ['enterAnomaly', 'exitAnomaly'])) {
            $wormhole = Wormhole::where("wormholeName", "=", strtoupper($request->value))->first();
            if (! $wormhole) {
                $signature->{$field} = null;
                $signature->save();
                return response()->json(['error' => 'Bad values'], 400);
            }
            $signature->{$field} = $wormhole->wormholeId;
            $signature->save();

            return response()->json(['status' => 'ok'], 200);
        }

        // 2.2 Set exit code
        if ($field == "exitCode") {
            if (! preg_match('/^([A-Za-z]{3})(-\d{0,3}|$)$/', $request->value, $output)) {
                $signature->exitCode = null;
                $signature->save();
                return response()->json(['error' => 'Bad values'], 400);
            }
            $signature->exitCode = $output[1] . (strlen($output[2] ?? '') == 4 ? $output[2] : '');
            $signature->save();

            return response()->json(['status' => 'ok'], 200);
        }

        // 2.3 Find System
        $system = System::where("solarSystemName", "=", $request->value)->first();
        if (! $system) {
            $signature->null;
            $signature->save();
            return response()->json(['error' => 'Bad values'], 400);
        }
        $signature->exitSystem = $system->solarSystemID;
        $signature->save();

        return response()->json(['status' => 'ok'], 200);
    }
}
