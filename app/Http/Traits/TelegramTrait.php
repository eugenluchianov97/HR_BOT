<?php


namespace App\Http\Traits;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait TelegramTrait
{

    /*-------------------------*/
    public $API_KEY;
    public $HOST;
    public $DOMAIN;
    public $URL;
    public $WEBHOOK_URL;

    /*-------------------------*/

    public function __construct(){
        $this->API_KEY = config('tg.API_KEY');
        $this->HOST = config('tg.HOST');
        $this->WEBHOOK_URL = config('tg.WEBHOOK_URL');
        $this->DOMAIN = config('tg.DOMAIN');

        $this->URL = $this->HOST .'/bot'. $this->API_KEY;
    }


    /*----------------------------------------------*/

    public function setWebhook(){
        return Http::post($this->URL . '/setWebhook?url='. $this->DOMAIN . $this->WEBHOOK_URL)->json();
    }


    public function deleteWebhook(){
        return Http::get($this->URL . '/setWebhook?url=')->json();
    }

    public function webhookInfo(){
        return Http::get($this->URL . '/getWebhookInfo')->json();
    }

    public function deleteMessage($message_id,$chat_id){

        $getQuery = array(
            'chat_id' =>  $chat_id,
            'message_id' => $message_id,
        );

        Http::get($this->URL  . '/deleteMessage?'. http_build_query($getQuery));
    }

    public function sendPhoto($chat_id,$photo_path,$caption){
        if(Storage::disk('public')->exists($photo_path)) {
            Http::attach('photo', file_get_contents(storage_path($photo_path)), $caption.'.jpg')
                ->post($this->URL.'/sendPhoto',[
                    'chat_id' => $chat_id,
                    'caption' => $caption,
                ]);
        }
    }

    public function sendDocument($chat_id,$doc_path,$caption){
        if(Storage::disk('public')->exists($doc_path)) {
            $ext = pathinfo($doc_path, PATHINFO_EXTENSION);
            Http::attach('document', file_get_contents(storage_path($doc_path)), $caption.'.'.$ext)
                ->post($this->URL.'/sendDocument',[
                    'chat_id' => $chat_id,
                    'caption' => $caption,
                ]);
        }
    }

    //отправялет сообщение
    public function sendMessage($chat_id,$text, $inline_keyboard = []){

        $getQuery = array(
            "chat_id" 	=> $chat_id,
            "text"  => $text,
            "parse_mode" => "HTML",
            "disable_web_page_preview" =>true,

        );

        if(count($inline_keyboard) > 0){
            $getQuery['reply_markup'] = json_encode(array(
                'inline_keyboard' => $inline_keyboard
            ));

        }


        Http::get($this->URL . '/sendMessage?'. http_build_query($getQuery));
    }

    public function getFile($file_id){
        $res = Http::get($this->URL . '/getFile?file_id='.$file_id);
        $file_path = $res['result']['file_path'];

        $res = ['code' => 400];

        if($file_path){
            $url = $this->HOST.'/file/bot'.$this->API_KEY.'/'.$file_path;
            $res['code'] = 200;
            $res['file'] = file_get_contents($url);
        }
        return $res;
    }


    public function button($text, $callback_data){
        return [
            'text' => $text,
            'callback_data' => json_encode($callback_data,true)
        ];
    }
    /*----------------------------------------------*/
}
