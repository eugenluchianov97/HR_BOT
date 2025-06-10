<?php


namespace App\Http\Controllers\Telegram;


use App\Http\Traits\TelegramTrait;
use App\Models\Candidate;
use Illuminate\Support\Facades\Log;

class LangController
{

    use TelegramTrait;

    public static function setLang(Candidate $candidate){
        $text = view('set_lang')->render();

        $buttons = [
            [
                self::button('🇲🇩',['step' => 'get.lang','lang' => 'ro']),
                self::button('🇷🇺',['step' => 'get.lang','lang' => 'ru'])
            ]
        ];

        self::sendMessage($candidate->chat_id,$text, $buttons);

    }

    public static function getLang(Candidate $candidate, $data){

        $candidate->lang = $data['lang'];
        $candidate->current_step = 'get.lang';
        $candidate->save();

        VacancyController::vacancyList($candidate);
    }
}
