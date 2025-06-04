<?php

namespace App\Http\Controllers;

use App\Http\Services\Telegram;
use App\Http\Traits\Data1CTrait;
use App\Http\Traits\FileTransferTrait;
use App\Http\Traits\TranslateTrait;
use App\Models\Candidate;
use App\Models\CandidateVacancies;
use App\Models\Document;
use App\Models\Emploe;
use App\Models\EmploeDocs;
use App\Models\EmployImage;
use App\Models\Vacancy;
use CURLFile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\FlareClient\Http\Client;
use thiagoalessio\TesseractOCR\TesseractOCR;
use App\Http\Traits\TelegramTrait;

use App\Http\Services\TelegramService;

use Telegram\Bot\Api;

class TelegrammController extends Controller
{

    use TelegramTrait;
    use TranslateTrait;
    use Data1CTrait;
    use FileTransferTrait;

    public $telegramService;

//    /*-------------------------*/
//    public $CANDIDATE_ID = null;
//    public $CANDIDATE = null;
//    public $MESSAGE_ID = null;
//    public $TEXT = null;
//    public $DATA = null;
//    public $PHOTO = null;
//    public $DOCUMENT = null;

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

        $this->telegramService = new TelegramService();
    }
    /*----------------------------------------------*/

    public function __invoke()
    {
        $webhookData =  (array) json_decode(file_get_contents("php://input"), true);

        Log::info($webhookData);

        $this->telegramService->__CANDIDATE($webhookData);

        $this->telegramService->__DATA($webhookData);

        $this->telegramService->__TEXT($webhookData);

        $this->telegramService->__MESSAGE_ID($webhookData);

        $this->telegramService->__PHOTO($webhookData);

        $this->telegramService->__DOCUMENT($webhookData);

        $this->telegramService->router();

    }

}
