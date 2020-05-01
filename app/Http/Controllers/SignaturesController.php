<?php

namespace App\Http\Controllers;

use App\Character;
use App\Library\EveApi_v2;
use Illuminate\Http\Request;
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

        $character = Character::find($arrEveData['characterId']);
        $system = $character->getSystem();
        $signatures = [];
        // TODO: pre-fill signatures

        return view('signatures.index', compact(['character', 'system', 'signatures']));
    }

    public function analyze(Request $request)
    {
        dd($request->rawdata);

        // TODO: find all "Cosmic Signature"
        // TODO: Detect their type and name if scanned
        // TODO: Add new to DB, remove old info from DB
        // TODO: let user choose the name for the wormholes via auto-complete field
        // TODO: save changes vis AJAX

        return redirect()->route('signatures');
    }
}
