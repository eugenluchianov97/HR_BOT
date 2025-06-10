<?php


namespace App\Http\Controllers\Telegram;


use App\Http\Traits\TelegramTrait;
use App\Models\Candidate;

class IDNPController
{
    use TelegramTrait;
    /** -----------#SET_IDNP-------- **/
    public static function setIDNP(Candidate $candidate,$error = false){
        $candidate->current_step = 'get.idnp';
        $candidate->save();

        $text = view('idnp',['lang' => $candidate->lang,'error' => $error])->render();

        $buttons = [
            [self::button(view('btn.undo',['lang' => $candidate->lang])->render(),['step' => 'info'])]
        ];

        self::sendMessage($candidate->chat_id,$text,$buttons);
    }

    /** -----------#GET_IDNP-------- **/
    public static function getIDNP(Candidate $candidate,$idnp){
        $employ = Candidate::where('IDNP', $idnp)->first();

        if($employ != null) self::setIDNP($candidate,'alreadyExists');
        elseif(!self::validate($idnp)) self::setIDNP($candidate,'notValid');

        if ($employ == null && self::validate($idnp)) {
            $candidate->IDNP = $idnp;
            $candidate->current_step = null;
            $candidate->save();

            InfoController::info($candidate);
        }
    }


    public static function validate($number) {
        if ( substr($number, 0, 2) !== "09" && substr($number, 0, 2) !== "20") {
            return false;
        }

        $multipliers = [7, 3, 1, 7, 3, 1, 7, 3, 1, 7, 3, 1];

        $weightedSum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = intval($number[$i]);
            $weightedSum += $digit * $multipliers[$i];
        }

        $calculatedControlDigit = $weightedSum % 10;
        $providedControlDigit = intval($number[12]);

        if ($calculatedControlDigit !== $providedControlDigit) {
            return false;
        }

        return true;
    }
}
