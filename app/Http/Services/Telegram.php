<?php


namespace App\Http\Services;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Telegram
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


    /*----------------------------------------------*/

}
