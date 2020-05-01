<?php

namespace App\Library;

use GuzzleHttp;
use Carbon\Carbon;
use Mockery\Exception;

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
        if(Carbon::now() < Carbon::createFromTimeString($arrEveData['expiresAt'])) {
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
     * Get Character Assets
     * @see : http://eveonline-third-party-documentation.readthedocs.io/en/latest/xmlapi/character/char_assetlist.html
     *
     * @param string $characterId,
     * @param string $accessToken,
     * @return array|bool
     */
    public static function getCharacterAssets($characterId, $accessToken)
    {

        $url = static::$esiUrl . "characters/{$characterId}/assets/?datasource=tranquility&token={$accessToken}";

        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', $url, [
            'http_errors' => false
        ]);

        if($response->getStatusCode() != 200) {
            throw new Exception("Cannot obtain EVE Character assets list");
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

    /**
     * Get Citadels names by id
     *
     * @param $arrIDs
     * @return array
     */
    public static function getCitadelNames($arrIDs)
    {

        $arrNames = [];

        foreach($arrIDs as $id) {
            try {
                $citadelDataJson = file_get_contents("https://stop.hammerti.me.uk/api/citadel/$id");
                $data = json_decode($citadelDataJson);
                $arrNames[$id] =  $data->{$id}->name;
            } catch (\Exception $e) {
                // do nothing
            }
        }

        return $arrNames;
    }

    /**
     * Get Types names
     *
     * @see https://esi.tech.ccp.is/ui/#/Universe/post_universe_names
     * @see https://esi.tech.ccp.is/ui/#/Universe/get_universe_types_type_id
     * @param array $arrNewItems
     * @return array
     */
    public static function getTypeNames($arrTypesIds)
    {
        $client = new GuzzleHttp\Client();

        $arrIDs = array_chunk($arrTypesIds, 1000);
        $arrNames = [];
        foreach($arrIDs as $arrChunk) {
            try {
                $response = $client->request('POST', static::$esiUrl . "universe/names/?datasource=tranquility", [
                    "json" => $arrChunk,
                ]);
            } catch(RequestException $e) {
                throw new Exception("Cannot obtain EVE Type names");
            }

            $statusCode = $response->getStatusCode();

            if($statusCode != 200) {
                throw new Exception("Cannot obtain EVE Type names");
            }

            foreach(json_decode($response->getBody()->getContents()) as $type) {
                $arrNames[$type->id] = $type->name;
            }
        }

        return $arrNames;
    }
}