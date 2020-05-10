<?php

namespace App;

use App\Library\EveApi_v2;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class Character extends Model
{
    protected $primaryKey = 'characterId';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'characterId', 'characterName', 'characterOwnerHash', 'accessToken', 'refreshToken', 'expiresAt'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public static function updateInfo($arrData)
    {
        $character = Character::firstOrNew(['characterId' => (string) $arrData['characterId']], $arrData);
        $character->characterOwnerHash = $arrData['characterOwnerHash'];
        $character->accessToken = $arrData['accessToken'];
        $character->refreshToken = $arrData['refreshToken'];
        $character->expiresAt = $arrData['expiresAt'];
        $character->save();
    }

    private function updateAccessToken()
    {
        $arrEveData = Session::get(\Config::get('constants.eve_data_session_variable'));
        $arrEveNewData = EveApi_v2::refreshTokens($arrEveData);

        if ($arrEveNewData['accessToken'] !== $arrEveData['accessToken']) {
            $arrEveNewData = array_merge($arrEveNewData, EveApi_v2::getCharacterId($arrEveNewData['accessToken']));
            Session::put(\Config::get('constants.eve_data_session_variable'), $arrEveNewData);
            $this->updateInfo($arrEveNewData);
        }

        return $arrEveNewData;
    }

    public function getSystem()
    {
        $this->updateAccessToken();
        $location = EveApi_v2::getCharacterLocation();

        return System::find($location->solar_system_id);

        //return $location;
    }
}
