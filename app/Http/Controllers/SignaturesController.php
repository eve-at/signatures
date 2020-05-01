<?php

namespace App\Http\Controllers;

use App\Character;
use App\Library\EveApi_v2;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class SignaturesController extends Controller
{
    public function index()
    {
        $arrEveData = Session::get(\Config::get('constants.eve_data_session_variable'));
        if (! $arrEveData) {
            return redirect()->route('index');
        }

        // update token
        $arrEveNewData = EveApi_v2::refreshTokens($arrEveData);
        if ($arrEveNewData['accessToken'] !== $arrEveData['accessToken']) {
            $arrEveNewData = array_merge($arrEveNewData, EveApi_v2::getCharacterId($arrEveNewData['accessToken']));
            Session::put(\Config::get('constants.eve_data_session_variable'), $arrEveNewData);
        }

        $this->saveCharacterInfo($arrEveNewData);

        //dd($arrEveData, $arrEveNewData);

        // TODO:

        return view('signatures.index');
    }

    private function saveCharacterInfo($arrData)
    {
        $character = Character::firstOrNew(['characterId' => (string) $arrData['characterId']]);
        $character->characterName = $arrData['characterName'];
        $character->characterOwnerHash = $arrData['characterOwnerHash'];
        $character->accessToken = $arrData['accessToken'];
        $character->refreshToken = $arrData['refreshToken'];
        $character->expiresAt = $arrData['expiresAt'];
        $character->save();
    }
}
