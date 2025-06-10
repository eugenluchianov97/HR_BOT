<?php


namespace App\Http\Controllers\Telegram;


use App\Http\Traits\TelegramTrait;
use App\Models\Candidate;
use Illuminate\Support\Facades\Http;

class InfoController
{
    use TelegramTrait;

    public static $GROUP_ID = '-4761952821';

    /** -----------#INFO-------- **/
    public static function info(Candidate $candidate){

        $hasName = $candidate->name != null;
        $hasDate = $candidate->date != null;
        $hasPhone = $candidate->phone != null;
        $hasIDNP = $candidate->IDNP != null;
        $hasLocation = $candidate->location != null;
        $hasProfession = count($candidate->vacancies) > 0;

        $text = view('info',[
            'lang' => $candidate->lang,
            'candidate' => $candidate,
        ])->render();

        $buttons = [
            [self::button(view('btn.name',['hasName' => $hasName,'lang' => $candidate->lang])->render(),['step' => 'set.name'])],
            [self::button(view('btn.date',['hasDate' => $hasDate,'lang' => $candidate->lang])->render(),['step' => 'set.date'])],
            [self::button(view('btn.phone',['hasPhone' => $hasPhone,'lang' => $candidate->lang])->render(),['step' => 'set.phone'])],
            [self::button(view('btn.location',['hasLocation' => $hasLocation,'lang' => $candidate->lang])->render(),['step' => 'set.location'])],
            [self::button(view('btn.idnp',['hasIDNP' => $hasIDNP,'lang' => $candidate->lang])->render(),['step' => 'set.idnp'])],
            [self::button(view('btn.vacancy',['hasVacancy' => $hasProfession,'lang' => $candidate->lang])->render(),['step' => 'vacancies.list', 'page' => 1])]
        ];

        if($hasName && $hasDate && $hasPhone && $hasIDNP && $hasProfession && $hasLocation) {
            $buttons[] =  [self::button(view('btn.send',['lang' => $candidate->lang])->render(),['step' => 'send1C'])];
        }

        self::sendMessage($candidate->chat_id,$text, $buttons);
    }



    /** -----------#SEND_1C-------- **/
    public static function send1c(Candidate $candidate) {
        $candidate->current_step = 'send.1C';
        $candidate->sendData = true;
        $candidate->save();
        self::thanks($candidate);
        $url = 'http://10.10.10.64:7766/erpbuh30/odata/standard.odata/InformationRegister_ex_%D0%9A%D0%B0%D0%BD%D0%B4%D0%B8%D0%B4%D0%B0%D1%82%D1%8B%D0%9D%D0%B0%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D1%83?&$format=json';


        $error = false;
        foreach ($candidate->vacancies as $vacancy) {

            $param = [
                'IDNP' => $candidate->IDNP,
                "ЧатИД" =>$candidate->chat_id,
                'ФИО' => $candidate->name,
                'Пол' => '',
                'ДатаРождения' => gmdate("Y-m-d", strtotime($candidate->date)),//1977-01-01T00:00:00
                'Должность_Key' => $vacancy->job_с_id,
                'Вакансия_Key' => $vacancy->ref_key,
                'Локации' => '',
                "АдресПроживания" => "",
                'Телефон' => $candidate->phone,
                'Email' => '',
                'ДатаРегистрации' => gmdate("Y-m-d\TH:i:s", time()),//2025-04-09T00:00:00
                'Статус' => "Принят",

            ];

            $res  = Http::withBasicAuth('exchange', 'saturn')
                ->withBody(json_encode($param,true),'application/json')
                ->post($url);
            $error = $res->status() != 200;
            sleep(1);
        }

        self::sendHR($candidate,$error);

    }

    /** -----------#SEND_HR-------- **/
    public static function sendHR(Candidate $candidate,$error = false){
        $text = view('send_hr',['error' => $error, 'candidate' => $candidate, 'lang' => $candidate->lang,])->render();

        self::sendMessage(self::$GROUP_ID,$text);
    }

    /** -----------#THANKS-------- **/
    public static function thanks(Candidate $candidate){
        self::sendMessage($candidate->chat_id,__('trans.thanks',[],$candidate->lang));
    }

}
