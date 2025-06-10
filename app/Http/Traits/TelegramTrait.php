<?php


namespace App\Http\Traits;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait TelegramTrait
{

    /*-------------------------*/
    public static $API_KEY = '7943493791:AAHxQrGRoabAOOICwoQ9KoGbmIoY7xJyDHY';
    public static $HOST = 'https://api.telegram.org';
    public static $DOMAIN = 'https://devhrbot.agg.md';
    public static $WEBHOOK_URL = '/telegram/response/0123456789';

    /*-------------------------*/

    public static function URL(){
        return self::$HOST .'/bot'. self::$API_KEY;
    }
    /*----------------------------------------------*/

    public static function setWebhook(){

        return Http::post(self::URL() . '/setWebhook?url='. self::$DOMAIN . self::$WEBHOOK_URL)->json();
    }


    public static function deleteWebhook(){
        return Http::get(self::URL() . '/setWebhook?url=')->json();
    }

    public static function webhookInfo(){
        return Http::get(self::URL() . '/getWebhookInfo')->json();
    }

    public  static function deleteMessage($message_id,$chat_id){

        $getQuery = array(
            'chat_id' =>  $chat_id,
            'message_id' => $message_id,
        );

        Http::get(self::URL()  . '/deleteMessage?'. http_build_query($getQuery));
    }

    public static function sendPhoto($chat_id,$photo_path,$caption){
        if(Storage::disk('public')->exists($photo_path)) {
            Http::attach('photo', file_get_contents(storage_path($photo_path)), $caption.'.jpg')
                ->post(self::URL().'/sendPhoto',[
                    'chat_id' => $chat_id,
                    'caption' => $caption,
                ]);
        }
    }

    public static function sendDocument($chat_id,$doc_path,$caption){
        if(Storage::disk('public')->exists($doc_path)) {
            $ext = pathinfo($doc_path, PATHINFO_EXTENSION);
            Http::attach('document', file_get_contents(storage_path($doc_path)), $caption.'.'.$ext)
                ->post(self::URL().'/sendDocument',[
                    'chat_id' => $chat_id,
                    'caption' => $caption,
                ]);
        }
    }

    //отправялет сообщение
    public static function sendMessage($chat_id,$text, $inline_keyboard = []){

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

        Log::info('sendMessage '.self::URL() . '/sendMessage?'. http_build_query($getQuery));
        Http::get( self::URL() . '/sendMessage?'. http_build_query($getQuery));
    }

    public static function getFile($file_id){
        $res = Http::get(self::URL() . '/getFile?file_id='.$file_id);
        $file_path = $res['result']['file_path'];

        $res = ['code' => 400];

        if($file_path){
            $url = self::$HOST.'/file/bot'. self::$API_KEY.'/'.$file_path;
            $res['code'] = 200;
            $res['file'] = file_get_contents($url);
        }
        return $res;
    }


    public static function button($text, $callback_data){
        return [
            'text' => $text,
            'callback_data' => json_encode($callback_data,true)
        ];
    }
    /*----------------------------------------------*/
}
