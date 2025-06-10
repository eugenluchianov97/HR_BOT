<?php


namespace App\Http\Controllers\Telegram;


use App\Http\Traits\TelegramTrait;
use App\Models\Candidate;

class PhoneController
{
    use TelegramTrait;

    /** -----------#SET_PHONE-------- **/
    public static function setPhone(Candidate $candidate){
        $candidate->current_step = 'get.phone';
        $candidate->save();

        $text = view('phone',['lang' => $candidate->lang])->render();

        $buttons = [
            [self::button(view('btn.undo',['lang' => $candidate->lang])->render(),['step' => 'info'])]
        ];

        self::sendMessage($candidate->chat_id,$text,$buttons);
    }

    /** -----------#GET_PHONE-------- **/
    public static function getPhone(Candidate $candidate, $phone){
        $candidate->phone = $phone;
        $candidate->current_step = null;
        $candidate->save();

        InfoController::info($candidate);
    }

}
