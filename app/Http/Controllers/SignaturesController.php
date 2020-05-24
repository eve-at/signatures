<?php

namespace App\Http\Controllers;

use App\Character;
use App\Exceptions\CharacterLocationException;
use App\Exceptions\ServerOfflineException;
use App\Library\EveApi_v2;
use App\Signature;
use App\Wormhole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class SignaturesController extends Controller
{
    protected $aErrors = [];

    public function index()
    {
        $arrEveData = Session::get(\Config::get('constants.eve_data_session_variable'));
        if (! $arrEveData) {
            return redirect()->route('index');
        }

        $character = Character::findOrFail($arrEveData['characterId']);
        try {
            $system = $character->getSystem();
        } catch (ServerOfflineException $e) {
            return redirect()->route('error/offline');
        } catch (CharacterLocationException $e) {
            return redirect()->route('index');
        }

        $signatures = Signature::with('ratings')->where(['enterSystem' => $system->solarSystemID])->orderBy('enterCode')->get();
        $anomalyStatic = $this->anomalyStatic;
        $anomalyDynamic = $this->anomalyDynamic;
        $wormholes = Wormhole::orderBy('wormholeName')->get();

        return view('signatures.index', compact(['arrEveData', 'character', 'system', 'signatures', 'anomalyStatic', 'anomalyDynamic', 'wormholes']));
    }

    private function validateAndFilter($aSignature)
    {
        if (1 !== preg_match('/([A-Z]{3}-\d{3})/', $aSignature['enterCode'], $output_array)) {
            return [
                'status' => 'error',
                'message' => 'Wrong format. Make sure you copy scanner data.',
            ];
        }

        if (! in_array($aSignature['signatureGroup'], [ 'Cosmic Signature'])) { //'Cosmic Anomaly',
            return [
                'status' => 'error',
                'message' => 'Cosmic Signatures are not supported',
            ];
        }

        if ($aSignature['anomalyGroup']
            && ! in_array($aSignature['anomalyGroup'], ['Combat Site', 'Ore Site', 'Gas Site', 'Data Site', 'Relic Site', 'Wormhole'])) {
            return [
                'status' => 'error',
                'message' => 'Wrong format. Make sure you copy scanner data.',
            ];
        }

        // TODO: validate $aSignature['anomalyName']
        $aSignature['anomalyName'] = null;

        return [
            'status' => 'ok',
            'signature' => $aSignature,
        ];
    }

    public function analyze(Request $request)
    {
        $arrEveData = Session::get(\Config::get('constants.eve_data_session_variable'));
        if (! $arrEveData) {
            return redirect()->route('index');
        }
        $character = Character::findOrFail($arrEveData['characterId']);
        $system = $character->getSystem();

        $lines = preg_split("/((\r?\n)|(\r\n?))/", $request->rawdata);
        foreach ($lines as $index => $line) {
            $aLine = explode("\t", $line);
            if (count($aLine) != 6) {
                $this->aErrors[] = "Line $index skipped: wrong format. Make sure you copy scanner data";
                continue;
            }
            $aLine = array_combine(['enterCode', 'signatureGroup', 'anomalyGroup', 'anomalyName', 'signal', 'distance'], $aLine);
            //dd($this->aErrors, explode("\t", $lines[0]), $lines, $aLine);

            $result = $this->validateAndFilter($aLine);
            if ($result['status'] == 'error') {
                $this->aErrors[] = "Line $index skipped: " . $result['message'];
                continue;
            }
            $aLine = $result['signature'];

            $signature = Signature::firstOrNew(
                [
                    'enterSystem' => $system->solarSystemID,
                    'characterId' => $character->characterId,
                    'enterCode'   => $aLine['enterCode'],
                ],
                [
                    'exitSystem' => null,
                    'exitCode' => null,
                    'enterAnomaly' => null,
                    'exitAnomaly' => null,
                    'expires_at' => Carbon::now()->addWeek()->format('Y-m-d H:i:s'),
                ]
            );

            $signature->signatureGroup = $aLine['signatureGroup'];
            $signature->anomalyGroup = $aLine['anomalyGroup'] ?: NULL;
            if ('Wormhole' == $aLine['anomalyGroup']) {
                $signature->expires_at = Carbon::parse($signature->created_at)->addDays(2)->format('Y-m-d H:i:s');
            }

            $signature->save();
        }

        return redirect()->route('signatures')->with('errors', $this->aErrors);
        //return Redirect::action('MembersController@loadRegisterView');
    }
}
