<?php


namespace App\Http\Controllers\Telegram;


use App\Http\Traits\TelegramTrait;
use App\Models\Candidate;

class LocationController
{
    use TelegramTrait;


    /** -----------#SET_LOCATION-------- **/
    public static function setLocation(Candidate $candidate){
        $candidate->current_step = 'get.location';//set_name
        $candidate->save();

        $text = view('location',['lang' => $candidate->lang])->render();

        $buttons = [
            [self::button(view('btn.undo',['lang' => $candidate->lang])->render(),['step' => 'info'])]
        ];

        self::sendMessage($candidate->chat_id,$text,$buttons);
    }

    /** -----------#GET_LOCATION-------- **/
    public static function getLocation(Candidate $candidate, $location){
        $candidate->location = $location;

        $candidate->current_step = null;
        $candidate->save();

        InfoController::info($candidate);
    }

}
