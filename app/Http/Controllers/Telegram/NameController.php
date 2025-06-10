<?php


namespace App\Http\Controllers\Telegram;


use App\Http\Traits\TelegramTrait;
use App\Models\Candidate;

class NameController
{
    use TelegramTrait;

    /** -----------#SET_NAME-------- **/
    public static function setName(Candidate $candidate){
        $candidate->current_step = 'get.name';//set_name
        $candidate->save();

        $text = view('name',['lang' => $candidate->lang])->render();

        $buttons = [
            [self::button(view('btn.undo',['lang' => $candidate->lang])->render(),['step' => 'info'])]
        ];

        self::sendMessage($candidate->chat_id,$text,$buttons);
    }

    /** -----------#GET_NAME-------- **/
    public static function getName(Candidate $candidate,$name){
        $candidate->name = $name;
        $candidate->current_step = null;

        $candidate->save();

        InfoController::info($candidate);
    }
}
