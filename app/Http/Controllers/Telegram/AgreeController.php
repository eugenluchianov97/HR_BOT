<?php


namespace App\Http\Controllers\Telegram;


use App\Http\Traits\TelegramTrait;
use App\Models\Candidate;
use Illuminate\Support\Facades\Log;

class AgreeController
{
    use TelegramTrait;

    /** -----------#SET_AGREE-------- **/
    public static function setAgree(Candidate $candidate){
        Log::info('AgreeController::setAgree');
        $text = view('agree',['lang' => $candidate->lang])->render();

        $buttons = [
            [self::button(view('btn.agree',['lang' => $candidate->lang])->render(),['step' => 'get.agree'])]
        ];

        self::sendMessage($candidate->chat_id,$text, $buttons);
    }

    /** -----------#GET_AGREE-------- **/
    public static function getAgree(Candidate $candidate){
        $candidate->agree = 1;
        $candidate->save();

        InfoController::info($candidate);
    }

    /** -----------#CHECK_AGREE-------- **/
    public static function checkAgree(Candidate $candidate){
        if($candidate->agree) InfoController::info($candidate);
        else AgreeController::setAgree($candidate);
    }
}
