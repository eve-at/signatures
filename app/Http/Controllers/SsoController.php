<?php

namespace App\Http\Controllers;

use App\Character;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp;
use GuzzleHttp\Exception\RequestException;
use App\Library\EveApi_v2;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Mockery\Exception;

class SsoController extends Controller
{

    private $code;

    /**
     * EVE SSO (Single Sign-On) Callback function
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $authCode = $request->input('code');

        if(empty($authCode)) {
            throw new Exception("Eve Authentication Failed");
        }

        $arrTokens = EveApi_v2::getAccessToken($authCode);

        $characterInfo = array_merge($arrTokens, EveApi_v2::getCharacterId($arrTokens['accessToken']));

        Session::put(\Config::get('constants.eve_data_session_variable'), $characterInfo);
        Character::updateInfo($characterInfo);

        return redirect()->route('index');
    }
}