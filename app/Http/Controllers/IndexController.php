<?php

namespace App\Http\Controllers;

use App\Character;
use App\Library\EveApi_v2;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    public function index()
    {
        $arrEveData = Session::get(\Config::get('constants.eve_data_session_variable'));
        if ($arrEveData) {
            return redirect()->route('signatures');
        }

        return view('index.welcome');
    }
}