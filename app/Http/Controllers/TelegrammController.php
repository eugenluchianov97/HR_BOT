<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Telegram\AgreeController;
use App\Http\Controllers\Telegram\DateController;
use App\Http\Controllers\Telegram\DocumentController;
use App\Http\Controllers\Telegram\IDNPController;
use App\Http\Controllers\Telegram\InfoController;
use App\Http\Controllers\Telegram\LocationController;
use App\Http\Controllers\Telegram\NameController;
use App\Http\Controllers\Telegram\PhoneController;
use App\Http\Controllers\Telegram\VacancyController;
use App\Http\Services\Telegram;
use App\Http\Traits\Data1CTrait;
use App\Http\Traits\FileTransferTrait;
use App\Models\Candidate;
use App\Models\CandidateDocuments;

use App\Models\Document;

use App\Models\Vacancy;
use CURLFile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\FlareClient\Http\Client;
use thiagoalessio\TesseractOCR\TesseractOCR;
use App\Http\Traits\TelegramTrait;

use App\Http\Controllers\Telegram\LangController;


use Telegram\Bot\Api;

class TelegrammController extends Controller
{

    use TelegramTrait;
    use Data1CTrait;
    use FileTransferTrait;

    /*-------------------------*/




    public $CANDIDATE = null;
    public $MESSAGE_ID = null;
    public $TEXT = null;
    public $DATA = null;
    public $PHOTO = null;
    public $DOCUMENT = null;

    public function __CANDIDATE($webhookData){
        $chat_id = null;
        if(isset($webhookData['message'])) {
            $chat_id = $webhookData['message']['from']['id'];
        }
        elseif(isset($webhookData['callback_query'])) {
            $chat_id = $webhookData['callback_query']['from']['id'];
        }
        elseif(isset($webhookData['my_chat_member'])) {
            $chat_id = $webhookData['my_chat_member']['from']['id'];
        }

        return Candidate::firstOrCreate(['chat_id' => $chat_id],['chat_id' => $chat_id]);
    }

    public function __DATA($webhookData){
        if(isset($webhookData['callback_query'])) {
            return json_decode($webhookData['callback_query']['data'],true);
        }
        return null;
    }

    public function __TEXT($webhookData){
        if(isset($webhookData['message'])) {
            return isset($webhookData['message']['text']) ? $webhookData['message']['text'] : null;
        }
        return null;
    }

    public function __MESSAGE_ID($webhookData){
        if(isset($webhookData['message'])) {
            return  $webhookData['message']['message_id'];
        }
        elseif(isset($webhookData['callback_query'])) {
            return $webhookData['callback_query']['message']['message_id'];
        }

        return null;
    }

    public function __PHOTO($webhookData){
        if(isset($webhookData['message']) && isset($webhookData['message']['photo'])){
            $f = $webhookData['message']['photo'][count($webhookData['message']['photo']) - 1];
            return [
                'type' => 'image',
                'file_id' => $f['file_id'],
                'file_ext' => 'png',
                'file_size' => $f['file_size'],
            ];
        }

        return null;
    }

    public function __DOCUMENT($webhookData){
        if(isset($webhookData['message']) && isset($webhookData['message']['document'])){
            $f = $webhookData['message']['document'];
            return  [
                'type' => 'document',
                'file_id' => $f['file_id'],
                'file_ext' => pathinfo($f['file_name'], PATHINFO_EXTENSION),
                'file_size' => $f['file_size'],
            ];
        }
        return null;
    }



    /*----------------------------------------------*/

    public function __invoke()
    {

        $webhookData =  (array) json_decode(file_get_contents("php://input"), true);

        Log::info($webhookData);


        $this->CANDIDATE = $this->__CANDIDATE($webhookData);

        $this->DATA =  $this->__DATA($webhookData);

        $this->TEXT = $this->__TEXT($webhookData);

        $this->MESSAGE_ID = $this->__MESSAGE_ID($webhookData);

        $this->PHOTO = $this->__PHOTO($webhookData);

        $this->DOCUMENT = $this->__DOCUMENT($webhookData);

        $this->router();

    }



    public function router(){

        Log::info('TEXT = ' . $this->TEXT);
        Log::info('DATA = ' . json_encode($this->DATA, true));
        Log::info('CANDIDATE' . $this->CANDIDATE->chat_id);
        Log::info('DOCUMENT' . json_encode($this->DOCUMENT,true));

        if(isset($this->DATA)) {
            if ($this->DATA['step'] == 'get.lang') LangController::getLang($this->CANDIDATE,$this->DATA);
            elseif($this->DATA['step'] == 'vacancies.list') VacancyController::vacancyList($this->CANDIDATE,$this->DATA['page']);
            elseif($this->DATA['step'] == 'vacancy.set') VacancyController::setVacancy($this->CANDIDATE,$this->DATA);
            elseif($this->DATA['step'] == 'vacancy.select') VacancyController::selectVacancy($this->CANDIDATE,$this->DATA);
            elseif($this->DATA['step'] == 'vacancy.delete') VacancyController::deleteVacancy($this->CANDIDATE,$this->DATA);
            elseif($this->DATA['step'] == 'check.agree') AgreeController::checkAgree($this->CANDIDATE);
            elseif($this->DATA['step'] == 'set.agree') AgreeController::setAgree($this->CANDIDATE);
            elseif($this->DATA['step'] == 'get.agree') AgreeController::getAgree($this->CANDIDATE);
            elseif($this->DATA['step'] == 'set.name') NameController::setName($this->CANDIDATE);
            elseif($this->DATA['step'] == 'set.location') LocationController::setLocation($this->CANDIDATE);
            elseif($this->DATA['step'] == 'set.date') DateController::setDate($this->CANDIDATE);
            elseif($this->DATA['step'] == 'set.phone') PhoneController::setPhone($this->CANDIDATE);
            elseif($this->DATA['step'] == 'set.idnp') IDNPController::setIDNP($this->CANDIDATE);

            elseif($this->DATA['step'] == 'send1C') InfoController::send1c($this->CANDIDATE);
            elseif($this->DATA['step'] == 'documents') DocumentController::documentsList($this->CANDIDATE,$this->DATA);
            elseif($this->DATA['step'] == 'document.upload') DocumentController::setDocument($this->CANDIDATE,$this->DATA);
            elseif($this->DATA['step'] == 'send_docs')  DocumentController::sendDocs($this->CANDIDATE,$this->DATA);
            elseif($this->DATA['step'] == 'info')  InfoController::info($this->CANDIDATE);
        }
        if(isset($this->TEXT)){
            if(explode(' ', $this->TEXT )[0] == '/start') VacancyController::vacancyById($this->CANDIDATE,$this->TEXT);
            if($this->TEXT == '/start') LangController::setLang($this->CANDIDATE);
            elseif($this->CANDIDATE->current_step == 'get.agree')AgreeController::getAgree($this->CANDIDATE);
            elseif($this->CANDIDATE->current_step == 'get.name') NameController::getName($this->CANDIDATE,$this->TEXT);
            elseif($this->CANDIDATE->current_step == 'get.location') LocationController::getLocation($this->CANDIDATE,$this->TEXT);
            elseif($this->CANDIDATE->current_step == 'get.date') DateController::getDate($this->CANDIDATE,$this->TEXT);
            elseif($this->CANDIDATE->current_step == 'get.phone') PhoneController::getPhone($this->CANDIDATE,$this->TEXT);
            elseif($this->CANDIDATE->current_step == 'get.idnp') IDNPController::getIDNP($this->CANDIDATE,$this->TEXT);
        }

        if(isset($this->PHOTO)){
            if(explode('|', $this->CANDIDATE->current_step)[0] == 'document') DocumentController::getPhoto($this->CANDIDATE,$this->PHOTO);
        }

        if(isset($this->DOCUMENT)){
            if(explode('|', $this->CANDIDATE->current_step)[0] == 'document') DocumentController::getDocument($this->CANDIDATE,$this->DOCUMENT);
        }

        $this->deleteMessage($this->MESSAGE_ID,$this->CANDIDATE->chat_id);
        $this->deleteMessage($this->MESSAGE_ID - 1,$this->CANDIDATE->chat_id);

    }

    public function callback(Request $request){
        $AUTH_USER = 'exchange';
        $AUTH_PASS = 'saturn';
        $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
        $is_not_authenticated = (!$has_supplied_credentials || $_SERVER['PHP_AUTH_USER'] != $AUTH_USER || $_SERVER['PHP_AUTH_PW'] != $AUTH_PASS);


        if ($is_not_authenticated) {
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');

            return response()->json(['code' => 401]);
        }

        $IDNP = $request->post('IDNP');
        $documents = $request->post('documents');

        $this->CANDIDATE = Candidate::where('IDNP',$IDNP)->first();

        if($this->CANDIDATE){

            DocumentController::CandidateDocuments($this->CANDIDATE,$documents);

            return response()->json(['code' => 201]);
        }
        else {
            return response()->json(['code' => 400,'message' => 'user not found']);
        }
    }

}
