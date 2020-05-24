<?php

namespace App\Http\Controllers;

use App\Rating;
use App\Signature;
use App\System;
use App\Wormhole;
use Carbon\Carbon;
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

        $items = collect($systems)->map(function ($system) {
            return [
                'id' => $system->solarSystemID,
                'text' => $system->toInfoString(),
            ];
        })->toArray();

        return [
            "total_count" => count($items),
            "items" => $items,
        ];
    }

    public function signature(Request $request)
    {
        $dataResponce = [
            'message' => '',
            'warning' => '',
            'summary' => '',
        ];

        if (! isset($request->field)) {
            $dataResponce['message'] = 'Missing fields';
            return response()->json(['status' => 'error', 'data' => $dataResponce], 400);
        }
        $arr = explode('_', $request->field);
        if (count($arr) != 2) {
            $dataResponce['message'] = 'Missing fields';
            return response()->json(['status' => 'error', 'data' => $dataResponce], 400);
        }
        list($field, $signatureId) = $arr;

        $anomalyInfo = array_merge($this->anomalyDynamic, $this->anomalyStatic);
        $fieldsWhiteList = collect(array_keys($anomalyInfo))->map(function ($key) {
            return 'anomaly' . $key;
        })->merge(['enterAnomaly', 'exitAnomaly', 'exitCode', 'exitSystem'])->toArray();
        if (! in_array($field, $fieldsWhiteList)) {
            $dataResponce['message'] = 'Bad values';
            return response()->json(['status' => 'error', 'data' => $dataResponce], 400);
        }

        // 1. Find character's signature
        $arrEveData = Session::get(\Config::get('constants.eve_data_session_variable'));

        $signature = Signature::where([
            ["characterId", "=", $arrEveData['characterId']],
            ["signatureId", "=", $signatureId],
        ])->first();
        if (! $signature) {
            $dataResponce['message'] = 'Bad values';
            return response()->json(['status' => 'error', 'data' => $dataResponce], 400);
        }

        // 2.1 Set Wormhole
        if (in_array($field , ['enterAnomaly', 'exitAnomaly'])) {
            $k162 = Wormhole::where("wormholeName", "=", "K162")->first();

            if (! $request->value) {
                $signature->{$field} = null;

                if (! $signature->enterAnomaly || ($signature->enterAnomaly == $k162->wormholeId && ! $signature->exitAnomaly)) {
                    $signature->anomalySize = null;
                    $signature->anomalyClass = null;
                }

                $signature->save();
                return response()->json(['status' => 'ok', 'summary' => $signature->summary()], 200);
            }

            $wormhole = Wormhole::where("wormholeName", "=", strtoupper($request->value))->first();
            if (! $wormhole) {
                $signature->{$field} = null;

                if (! $signature->enterAnomaly && ! $signature->exitAnomaly) {
                    $signature->anomalySize = null;
                    $signature->anomalyClass = null;
                }

                $signature->save();
                return response()->json(['error' => 'Bad values', 'summary' => $signature->summary()], 400);
            }

            $signature->{$field} = $wormhole->wormholeId;

            // set another side WH
            $anotherSideTitle = ($field == 'enterAnomaly') ? 'exitAnomaly' : 'enterAnomaly';
            if ($wormhole->wormholeId == $k162->wormholeId) {
                if ($signature->{$anotherSideTitle} == $k162->wormholeId) {
                    $signature->{$anotherSideTitle} = null;
                }
            } else {
                $signature->anomalySize = null;
                $signature->anomalyClass = null;
                $signature->{$anotherSideTitle} = $k162->wormholeId;
            }

            // Update expire date
            if ($wormhole->maxStableTime > 0) {
                $signature->expires_at = Carbon::parse($signature->created_at)->addHours($wormhole->maxStableTime)->toDateTimeString();
            }

            $signature->save();

            $anotherSideWormhole = $signature->{$anotherSideTitle}();
            $data = [
                //'Size'  => $wormhole->wormholeSize(),
                'Class' => $wormhole->wormholeClass(true),
                //'ClassGrouped' => $wormhole->wormholeClass(),
                'AnotherSideWormhole' => $anotherSideWormhole ? $anotherSideWormhole->wormholeName : '',
            ];

            return response()->json(['status' => 'ok', 'data' => $data, 'summary' => $signature->summary()], 200);
        }

        // 2.2 Set exit code
        if ($field == "exitCode") {
            if (! $request->value) {
                $signature->exitCode = null;
                $signature->save();
                return response()->json(['status' => 'ok', 'summary' => $signature->summary()], 200);
            }

            if (! preg_match('/^([A-Za-z]{3})(-\d{0,3}|$)$/', strtoupper($request->value), $output)) {
                $signature->exitCode = null;
                $signature->save();
                return response()->json(['error' => 'Bad values', 'summary' => $signature->summary()], 400);
            }

            $signature->exitCode = $output[1] . (strlen($output[2] ?? '') == 4 ? $output[2] : '');
            $signature->save();
            return response()->json(['status' => 'ok', 'summary' => $signature->summary()], 200);
        }

        // 2.3 Find System
        if ($field == "exitSystem") {
            if (! $request->value) {
                $signature->exitSystem = null;
                $signature->save();
                return response()->json(['status' => 'ok', 'summary' => $signature->summary()], 200);
            }

            $system = System::find($request->value);
            if (! $system) {
                $signature->exitSystem = null;
                $signature->save();
                return response()->json(['error' => 'Bad values', 'summary' => $signature->summary()], 400);
            }

            $signature->exitSystem = $system->solarSystemID;
            $signature->save();
            return response()->json(['status' => 'ok', 'summary' => $signature->summary()], 200);
        }

        // 2.4 Anomaly Info
        $key = str_replace('anomaly', '', $field);
        if (isset($anomalyInfo[$key])) {
            $data = [
                'Size'  => '',
                'Class' => '',
            ];
            if (! $request->value) {
                $signature->{$field} = null;
                $signature->save();
                return response()->json(['status' => 'ok', 'data' => $data, 'summary' => $signature->summary()], 200);
            }

            if (in_array($request->value, $anomalyInfo[$key])) {
                $signature->{$field} = $request->value;
                $signature->save();

                // TODO:
                $data = [
                    'Size'  => '',
                    'Class' => '',
                ];
                return response()->json(['status' => 'ok', 'data' => $data, 'summary' => $signature->summary()], 200);
            }

            $signature->{$field} = null;
            $signature->save();
            return response()->json(['status' => 'error', 'message' => 'Bad values', 'summary' => $signature->summary()], 400);

        }

        return response()->json(['error' => 'Bad values', 'summary' => $signature->summary()], 400);
    }

    public function signatureDelete(Request $request)
    {
        if (! isset($request->value)) {
            return response()->json(['error' => 'Missing fields'], 400);
        }

        $arrEveData = Session::get(\Config::get('constants.eve_data_session_variable'));

        // can remove only own signatures
        $signature = Signature::where([
            ["characterId", "=", $arrEveData['characterId']],
            ["signatureId", "=", $request->value],
        ])->first();
        if (! $signature) {
            return response()->json(['error' => 'Bad values8'], 400);
        }

        $signature->forceDelete();

        return response()->json(['status' => 'ok'], 200);
    }


    public function signatureLike(Request $request)
    {
        if (! isset($request->id) || ! isset($request->like)) {
            return response()->json(['error' => 'Missing fields'], 400);
        }

        $arrEveData = Session::get(\Config::get('constants.eve_data_session_variable'));

        $signature = Signature::where([
            ["signatureId", "=", $request->id],
        ])->first();
        if (! $signature) {
            return response()->json(['error' => 'Bad values9'], 400);
        }

        // can't rate your own signature
        if ($signature->characterId == $arrEveData['characterId']) {
            return response()->json(['error' => 'Bad values10'], 400);
        }

        $rating = Rating::firstOrNew([
            ["characterId", "=", $arrEveData['characterId']],
            ["signatureId", "=", $signature->signatureId],
        ]);
        $rating->characterId = $arrEveData['characterId'];
        $rating->signatureId = $signature->signatureId;
        $rating->characterName = $arrEveData['characterName'];
        $rating->liked = !! $request->like;
        $rating->save();

        return response()->json(['status' => 'ok'], 200);
    }
}
