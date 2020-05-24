<?php

namespace App\Library;

use App\Character;
use App\Exceptions\ServerOfflineException;
use GuzzleHttp;
use Carbon\Carbon;
use Mockery\Exception;
use Illuminate\Support\Facades\Session;

/**
 * Class EveApi_v2
 * @package App\Library
 * @see https://esi.evetech.net/latest/?datasource=tranquility
 */
class EveApi_v2
{

    protected static $esiUrl = "https://esi.evetech.net/latest/";

    /**
     * Get the character ID from the SSO server
     * @see : http://eveonline-third-party-documentation.readthedocs.io/en/latest/sso/obtaincharacterid.html
     * @param string $accessToken
     * @return array
     */
    public static function getCharacterId($accessToken)
    {
        $client = new GuzzleHttp\Client();
        $requestParams = [
            "headers" => [
                "Authorization" => 'Bearer ' . $accessToken,
                "Host"          => "login.eveonline.com",
            ],
        ];

        try {
            $response = $client->get('https://login.eveonline.com/oauth/verify', $requestParams);
        } catch(RequestException $e) {
            return response()->view('error.missing', [], 503);
        }

        $statusCode = $response->getStatusCode();

        if($statusCode != 200) {
            throw new Exception("Cannot obtain EVE Character Data");
        }

        $responseBody = json_decode($response->getBody()->getContents());

        return [
            "characterId" => $responseBody->CharacterID,
            "characterName" => $responseBody->CharacterName,
            "characterOwnerHash" => $responseBody->CharacterOwnerHash,
        ];
    }

    /**
     * Obtain access token
     *
     * @param string $authCode
     * @return array
     */
    public static function getAccessToken($authCode)
    {
        $client = new GuzzleHttp\Client();
        $requestParams = [
            "headers" => [
                "Authorization" => "Basic " . base64_encode(config('app.eve_app_id').":".config('app.eve_app_secret')),
                "Content-Type"  => "application/x-www-form-urlencoded",
                "Host"          => "login.eveonline.com",
            ],
            "form_params" => [
                "grant_type" => "authorization_code",
                "code"       => $authCode,
            ],
            'http_errors' => false
        ];
        $response = $client->post('https://login.eveonline.com/oauth/token', $requestParams);
        if($response->getStatusCode() != 200) {
            throw new Exception("Cannot obtain EVE Access Token");
        }

        /* Example : $response->getBody()->getContents()
        {
            "access_token":"NUkgo8GIT6fEruK5B84iQQDiGFXrd58AdPtPHHYlm-2c0SnoMgYPqa9ZIiCMa0gMRsOkBtVV2omNkOgk575z7A2",
            "token_type":"Bearer",
            "expires_in":1199,
            "refresh_token":"gG00vmVn2HoJHs40zFUfbzuQBmt1VuMO5IqmkcVK3bbRBMI1sDWAuyitg_ZxUApRD3D_N83fqU4IccXAcuRalg2"
        }
        */

        $responseBody = json_decode($response->getBody()->getContents());

        return [
            'accessToken'  => $responseBody->access_token,
            'refreshToken' => $responseBody->refresh_token,
            'expiresAt'    => Carbon::now()->addSeconds($responseBody->expires_in)->toDateTimeString(),
        ];
    }

    /**
     * Refresh access token
     *
     * @param array $arrEveData
     * @return array
     */
    public static function refreshTokens($arrEveData)
    {
        if (Carbon::now() < Carbon::createFromTimeString($arrEveData['expiresAt'])) {
            return $arrEveData;
        };

        $client = new GuzzleHttp\Client();
        $requestParams = [
            "headers" => [
                "Authorization" => "Basic " . base64_encode(config('app.eve_app_id').":".config('app.eve_app_secret')),
                "Content-Type"  => "application/x-www-form-urlencoded",
                "Host"          => "login.eveonline.com",
            ],
            "form_params" => [
                "grant_type"    => "refresh_token",
                "refresh_token" => $arrEveData['refreshToken'],
            ],
            'http_errors' => false
        ];
        $response = $client->post('https://login.eveonline.com/oauth/token', $requestParams);
        if($response->getStatusCode() != 200) {
            throw new Exception("Cannot obtain EVE Access Token");
        }

        /* Example : $response->getBody()->getContents()
        {
            "access_token":"NUkgo8GIT6fEruK5B84iQQDiGFXrd58AdPtPHHYlm-2c0SnoMgYPqa9ZIiCMa0gMRsOkBtVV2omNkOgk575z7A2",
            "token_type":"Bearer",
            "expires_in":1199,
            "refresh_token":"gG00vmVn2HoJHs40zFUfbzuQBmt1VuMO5IqmkcVK3bbRBMI1sDWAuyitg_ZxUApRD3D_N83fqU4IccXAcuRalg2"
        }
        */

        $responseBody = json_decode($response->getBody()->getContents());

        return [
            'accessToken'  => $responseBody->access_token,
            'refreshToken' => $responseBody->refresh_token,
            'expiresAt'    => Carbon::now()->addSeconds($responseBody->expires_in)->toDateTimeString(),
        ];
    }

    /**
     * EVE Server status
     * This route is cached for up to 30 seconds
     * @see : https://esi.evetech.net/latest/status/?datasource=tranquility
     *
     * @return bool
     */
    public static function serverIsOnline()
    {
        $url = static::$esiUrl . "status/?datasource=tranquility";

        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', $url, [
            'http_errors' => false
        ]);

        if ($response->getStatusCode() != 200) {
            return false;
        }

        $content = json_decode($response->getBody()->getContents());
        return !! ($content->players ?? 0);
    }

    /**
     * Information about the characters current location.
     * Returns the current solar system id, and also the current station or structure ID if applicable
     * @see : https://esi.evetech.net/ui/#/Location/get_characters_character_id_location
     *
     * @param Character $character,
     * @return array|bool
     */
    public static function getCharacterLocation()
    {
        $arrEveData = Session::get(\Config::get('constants.eve_data_session_variable'));

        $url = static::$esiUrl . "characters/{$arrEveData['characterId']}/location/?datasource=tranquility&token={$arrEveData['accessToken']}";

        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', $url, [
            'http_errors' => false
        ]);

        if ($response->getStatusCode() != 200) {
            if (! static::serverIsOnline()) {
                throw new ServerOfflineException();
            }
            throw new CharacterLocationException("Cannot obtain EVE Character location");
        }

        /* Example : $response->getBody()->getContents()
        array: [▼
              0 => {
                +"type_id": 34
                +"quantity": 1
                +"location_id": 1026503095686
                +"location_type": "other"
                +"item_id": 1026503095692
                +"location_flag": "Cargo"
                +"is_singleton": false
              }
        ];
        */

        return json_decode($response->getBody()->getContents());
    }

}