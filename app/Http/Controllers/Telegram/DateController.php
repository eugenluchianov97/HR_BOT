<?php


namespace App\Http\Controllers\Telegram;


use App\Http\Traits\TelegramTrait;
use App\Models\Candidate;

class DateController
{
    use TelegramTrait;


    /** -----------#SET_DATE-------- **/
    public static function setDate(Candidate $candidate){
        $candidate->current_step = 'get.date';
        $candidate->save();

        $text = view('date',['lang' => $candidate->lang])->render();

        $buttons = [
            [self::button(view('btn.undo',['lang' => $candidate->lang])->render(),['step' => 'info'])]
        ];

        self::sendMessage($candidate->chat_id,$text,$buttons);
    }

    /** -----------#GET_DATE-------- **/
    public static function getDate(Candidate $candidate, $date){
        $candidate->date = $date;

        $candidate->current_step = null;
        $candidate->save();

        InfoController::info($candidate);
    }



}
