<?php


namespace App\Http\Services;


use App\Http\Traits\Data1CTrait;
use App\Http\Traits\FileTransferTrait;
use App\Http\Traits\TelegramTrait;
use App\Models\Candidate;
use App\Models\CandidateDocuments;
use App\Models\Day;
use App\Models\Document;
use App\Models\Requirement;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class TelegramService
{
    use TelegramTrait;
    use Data1CTrait;
    use FileTransferTrait;

    public $telegram;

    public $GROUP_ID = '-4761952821';

    public $CANDIDATE = null;
    public $MESSAGE_ID = null;
    public $TEXT = null;
    public $DATA = null;
    public $PHOTO = null;
    public $DOCUMENT = null;

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


    public function __CANDIDATE($webhookData){
        $chat_id = null;
        if(isset($webhookData['message'])) {
            $chat_id = $webhookData['message']['from']['id'];
        }
        elseif(isset($webhookData['callback_query'])) {
            $chat_id = $webhookData['callback_query']['from']['id'];
        }
        elseif(isset($webhookData['my_chat_member'])) {
            $chat_id = $webhookData['my_chat_member']['message']['from']['id'];
        }

        $this->CANDIDATE = Candidate::firstOrCreate(['chat_id' => $chat_id],['chat_id' => $chat_id]);
    }

    public function __DATA($webhookData){
        if(isset($webhookData['callback_query'])) {
            $this->DATA = json_decode($webhookData['callback_query']['data'],true);
        }
    }

    public function __TEXT($webhookData){
        if(isset($webhookData['message'])) {
            $this->TEXT = isset($webhookData['message']['text']) ? $webhookData['message']['text'] : null;
        }
    }

    public function __MESSAGE_ID($webhookData){
        if(isset($webhookData['message'])) {
            $this->MESSAGE_ID = $webhookData['message']['message_id'];
        }
        elseif(isset($webhookData['callback_query'])) {
            $this->MESSAGE_ID = $webhookData['callback_query']['message']['message_id'];
        }
    }

    public function __PHOTO($webhookData){
        if(isset($webhookData['message']) && isset($webhookData['message']['photo'])){
            $f = $webhookData['message']['photo'][count($webhookData['message']['photo']) - 1];
            $this->PHOTO = [
                'type' => 'image',
                'file_id' => $f['file_id'],
                'file_ext' => 'png',
                'file_size' => $f['file_size'],
            ];
        }
    }

    public function __DOCUMENT($webhookData){
        if(isset($webhookData['message']) && isset($webhookData['message']['document'])){
            $f = $webhookData['message']['document'];
            $this->DOCUMENT = [
                'type' => 'document',
                'file_id' => $f['file_id'],
                'file_ext' => pathinfo($f['file_name'], PATHINFO_EXTENSION),
                'file_size' => $f['file_size'],
            ];
        }
    }
    /*-----------------------------------------------------------------*/

    public function router(){

        Log::info('TEXT = ' . $this->TEXT);
        Log::info('DATA = ' . json_encode($this->DATA, true));
        Log::info('CANDIDATE' . $this->CANDIDATE->chat_id);
        Log::info('DOCUMENT' . json_encode($this->DOCUMENT,true));

        $this->deleteMessage($this->MESSAGE_ID,$this->CANDIDATE->chat_id);
        $this->deleteMessage($this->MESSAGE_ID - 1,$this->CANDIDATE->chat_id);

        if(isset($this->DATA)) {

            if ($this->DATA['step'] == 'get.lang') $this->getLang();

            elseif($this->DATA['step'] == 'vacancies.list') $this->vacanciesList();
            elseif($this->DATA['step'] == 'vacancy.set') $this->setVacancy();
            elseif($this->DATA['step'] == 'vacancy.select') $this->selectVacancy();
            elseif($this->DATA['step'] == 'vacancy.delete') $this->deleteVacancy();

            elseif($this->DATA['step'] == 'check.agree') $this->checkAgree();
            elseif($this->DATA['step'] == 'set.agree') $this->setAgree();
            elseif($this->DATA['step'] == 'get.agree') $this->getAgree();

            elseif($this->DATA['step'] == 'set.name') $this->setName();
            elseif($this->DATA['step'] == 'set.location') $this->setLocation();
            elseif($this->DATA['step'] == 'set.date') $this->setDate();
            elseif($this->DATA['step'] == 'set.phone') $this->setPhone();
            elseif($this->DATA['step'] == 'set.idnp') $this->setIDNP();

            elseif($this->DATA['step'] == 'send1C') $this->send1c();
            elseif($this->DATA['step'] == 'documents') $this->documentsList();
            elseif($this->DATA['step'] == 'document.upload') $this->setDocument();
            elseif($this->DATA['step'] == 'send_docs') $this->sendDocs();
            elseif($this->DATA['step'] == 'info') $this->info();
        }
        if(isset($this->TEXT)){
            if($this->TEXT == '/start') $this->setLang();
            else if($this->CANDIDATE->current_step == 'get.agree') $this->getAgree();
            else if($this->CANDIDATE->current_step == 'get.name') $this->getName();
            else if($this->CANDIDATE->current_step == 'get.location') $this->getLocation();
            else if($this->CANDIDATE->current_step == 'get.date') $this->getDate();
            else if($this->CANDIDATE->current_step == 'get.phone') $this->getPhone();
            else if($this->CANDIDATE->current_step == 'get.idnp') $this->getIDNP();
        }

        if(isset($this->PHOTO)){
           if(explode('|', $this->CANDIDATE->current_step)[0] == 'document') $this->getPhoto();
        }

        if(isset($this->DOCUMENT)){
            if(explode('|', $this->CANDIDATE->current_step)[0] == 'document') $this->getDocument();
        }
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
        $this->CANDIDATE = Candidate::where('IDNP',$IDNP)->first();

        if($this->CANDIDATE){
            $this->CANDIDATE->access = true;
            $this->CANDIDATE->save();
            $this->documents();


            return response()->json(['code' => 201]);
        }
        else {
            return response()->json(['code' => 400,'message' => 'user not found']);
        }
    }



    /**---------------#VACANCIES_LIST----------------------**/
    public function vacanciesList($page = 1){
        $this->CANDIDATE->current_step = 'vacancies.list';//vacancies
        $this->CANDIDATE->save();

        $per_page = 10;

        $vacancies = Vacancy::where('status', 1)->paginate($per_page, ['*'], 'page', $page);

        $row1 = [];
        $row2 = [];
        $row3 = [];
        $row4 = [];

        $text = view('vacancies',[
            'vacancies' => $vacancies->items(),
            'lang' => $this->CANDIDATE->lang,
            'candidate' =>$this->CANDIDATE,
            'page' => $page,
            'per_page' => $per_page
        ])->render();

        if(count($vacancies) > 0) {
            $t = ceil(count($vacancies) / 2);
            foreach($vacancies->items() as $index => $vacancy) {
                $idx = (($page -1) * $per_page + $index)+1;
                $btn = $this->button($idx,['step' => 'vacancy.set','page' => $page,'id' => $vacancy['id']]);

                if($index < $t) $row1[] = $btn;
                if($index >= $t) $row2[] = $btn;
            }
        }

        if($page > 1) $row3[] = $this->button('⬅',['step' => 'vacancies.list','page' => $page-1]);

        if($page < $vacancies->lastPage()) $row3[] = $this->button('➡',['step' => 'vacancies.list','page' => $page+1]);

        if(count($this->CANDIDATE->vacancies) > 0) $row4[] = $this->button(__('trans.click_now',[],$this->CANDIDATE->lang),['step' => 'check.agree']);

        $buttons = [$row1, $row2, $row3,$row4];

        $this->sendMessage($this->CANDIDATE->chat_id,$text, $buttons);
    }

    /**---------------#VACANCY----------------------**/
    public function vacancy($id,$page = 1){
        $vacancy = Vacancy::find($id);
        if($vacancy){
            $text = view('vacancy',[
                'vacancy' => $vacancy,
                'has_vacancy' => $this->CANDIDATE->hasVacancy($vacancy->id),
                'lang' => $this->CANDIDATE->lang,
            ])->render();

            $row1 = [];
            $row2 = [];

            if($this->CANDIDATE->hasVacancy($vacancy->id)){
                $row1[] = $this->button(__('trans.delete_vacancy',[],$this->CANDIDATE->lang),['step' => 'vacancy.delete','id' => $vacancy['id']]);
            }
            else {
                $row1[]  = $this->button(__('trans.select_vacancy',[],$this->CANDIDATE->lang),['step' => 'vacancy.select','id' => $vacancy['id']]);
            }

            $row1[] = $this->button(__('trans.back_to_list',[],$this->CANDIDATE->lang),['step' => 'vacancies.list','page' => $page]);

            if(count($this->CANDIDATE->vacancies) > 0){
                $row2[] = $this->button(__('trans.click_now',[],$this->CANDIDATE->lang),['step' => 'check.agree']);
            }

            $buttons = [ $row1,$row2];

            $this->sendMessage($this->CANDIDATE->chat_id,$text,$buttons);
        }
    }

    /**---------------#SET_VACANCY----------------------**/
    public function setVacancy(){
        $this->CANDIDATE->current_step = $this->DATA['step'];//vacancyItem
        $this->CANDIDATE->save();
        $this->vacancy($this->DATA['id'], $this->DATA['page']);
    }

    /**---------------#SELECT_VACANCY----------------------**/
    public function selectVacancy(){
        $this->CANDIDATE->current_step = $this->DATA['step'];//vacancyItem
        $this->CANDIDATE->save();

        $vacancy = Vacancy::find($this->DATA['id']);

        $this->CANDIDATE->vacancies()->attach($vacancy->id);
        $this->CANDIDATE->load('vacancies');

        foreach($vacancy->documents as $document){

            $this->CANDIDATE->documents()->attach($document->id,[
                    'src' => null,
                    'vacancy_id' => $vacancy->id,
                    'required' => $document->pivot->required,
                    'type' => null
                ]);
        }

        $this->vacancy($this->DATA['id']);
    }

    /**---------------#DELETE_VACANCY----------------------**/
    public function deleteVacancy(){
        $this->CANDIDATE->current_step = $this->DATA['step'];//vacancyItem
        $this->CANDIDATE->save();

        $vacancy = Vacancy::find($this->DATA['id']);


        $this->CANDIDATE->vacancies()->detach($vacancy->id);
        $this->CANDIDATE->load('vacancies');

        CandidateDocuments::where('candidate_id',$this->CANDIDATE->id)->where('vacancy_id',$vacancy->id)->delete();

        $this->vacancy($this->DATA['id']);
    }

    /**---------------#SET_LANG-------------------------------**/
    public function setLang(){
        $text = view('set_lang')->render();

        $btn1 = $this->button('🇲🇩',['step' => 'get.lang','lang' => 'ro']);

        $btn2 = $this->button('🇷🇺',['step' => 'get.lang','lang' => 'ru']);

        $buttons = [[$btn1, $btn2]];

        $this->sendMessage($this->CANDIDATE->chat_id,$text, $buttons);
    }

    /**---------------#GET_LANG----------------------**/
    public function getLang(){
        $this->CANDIDATE->lang = $this->DATA['lang'];
        $this->CANDIDATE->current_step = 'get.lang';
        $this->CANDIDATE->save();

        $this->vacanciesList();
    }

    /** -----------#SET_NAME-------- **/
    public function setName(){
        $this->CANDIDATE->current_step = 'get.name';//set_name
        $this->CANDIDATE->save();

        $text = view('name',['lang' => $this->CANDIDATE->lang])->render();

        $buttons = [
            [$this->button(view('btn.undo',['lang' => $this->CANDIDATE->lang])->render(),['step' => 'info'])]
        ];

        $this->sendMessage($this->CANDIDATE->chat_id,$text,$buttons);
    }

    /** -----------#GET_NAME-------- **/
    public function getName(){
        $this->CANDIDATE->name = $this->TEXT;

        $this->CANDIDATE->current_step = null;
        $this->CANDIDATE->save();

        $this->info();
    }

    /** -----------#SET_IDNP-------- **/
    public function setIDNP($error = false){
        $this->CANDIDATE->current_step = 'get.idnp';
        $this->CANDIDATE->save();

        $text = view('idnp',['lang' => $this->CANDIDATE->lang,'error' => $error])->render();

        $buttons = [
            [$this->button(view('btn.undo',['lang' => $this->CANDIDATE->lang])->render(),['step' => 'info'])]
        ];

        $this->sendMessage($this->CANDIDATE->chat_id,$text,$buttons);
    }

    /** -----------#GET_IDNP-------- **/
    public function getIDNP(){
        $employ = Candidate::where('IDNP', $this->TEXT)->first();

        $Valid = $this->validateNumber($this->TEXT);

        if($employ != null)$this->setIDNP('alreadyExists');
        elseif(!$Valid) $this->setIDNP('notValid');

        if ($employ == null && $Valid) {
            $this->CANDIDATE->IDNP = $this->TEXT;
            $this->CANDIDATE->current_step = null;
            $this->CANDIDATE->save();

            $this->info();
        }
    }

    /** -----------#SET_PHONE-------- **/
    public function setPhone(){
        $this->CANDIDATE->current_step = 'get.phone';
        $this->CANDIDATE->save();

        $text = view('phone',['lang' => $this->CANDIDATE->lang])->render();

        $buttons = [
            [$this->button(view('btn.undo',['lang' => $this->CANDIDATE->lang])->render(),['step' => 'info'])]
        ];

        $this->sendMessage($this->CANDIDATE->chat_id,$text,$buttons);
    }

    /** -----------#GET_PHONE-------- **/
    public function getPhone(){
        $this->CANDIDATE->phone = $this->TEXT;

        $this->CANDIDATE->current_step = null;
        $this->CANDIDATE->save();

        $this->info();
    }

    /** -----------#SET_LOCATION-------- **/
    public function setLocation(){
        $this->CANDIDATE->current_step = 'get.location';//set_name
        $this->CANDIDATE->save();

        $text = view('location',['lang' => $this->CANDIDATE->lang])->render();

        $buttons = [
            [$this->button(view('btn.undo',['lang' => $this->CANDIDATE->lang])->render(),['step' => 'info'])]
        ];

        $this->sendMessage($this->CANDIDATE->chat_id,$text,$buttons);
    }

    /** -----------#GET_LOCATION-------- **/
    public function getLocation(){
        $this->CANDIDATE->location = $this->TEXT;

        $this->CANDIDATE->current_step = null;
        $this->CANDIDATE->save();

        $this->info();
    }

    /** -----------#SET_DATE-------- **/
    public function setDate(){
        $this->CANDIDATE->current_step = 'get.date';
        $this->CANDIDATE->save();

        $text = view('date',['lang' => $this->CANDIDATE->lang])->render();

        $buttons = [
            [$this->button(view('btn.undo',['lang' => $this->CANDIDATE->lang])->render(),['step' => 'info'])]
        ];

        $this->sendMessage($this->CANDIDATE->chat_id,$text,$buttons);
    }

    /** -----------#GET_DATE-------- **/
    public function getDate(){
        $this->CANDIDATE->date = $this->TEXT;

        $this->CANDIDATE->current_step = null;
        $this->CANDIDATE->save();

        $this->info();
    }

    /** -----------#SET_AGREE-------- **/
    public function setAgree(){

        $text = view('agree',['lang' => $this->CANDIDATE->lang])->render();

        $buttons = [
            [$this->button(view('btn.agree',['lang' => $this->CANDIDATE->lang])->render(),['step' => 'get.agree'])]
        ];

        $this->sendMessage($this->CANDIDATE->chat_id,$text, $buttons);
    }

    /** -----------#GET_AGREE-------- **/
    public function getAgree(){

        $this->CANDIDATE->agree = 1;

        $this->CANDIDATE->save();

        $this->info();
    }

    /** -----------#CHECK_AGREE-------- **/
    public function checkAgree(){
        if($this->CANDIDATE->agree)$this->info();
        else $this->setAgree();
    }

    /** -----------#INFO-------- **/
    public function info(){

        $hasName = $this->CANDIDATE->name != null;
        $hasDate = $this->CANDIDATE->date != null;
        $hasPhone = $this->CANDIDATE->phone != null;
        $hasIDNP = $this->CANDIDATE->IDNP != null;
        $hasLocation = $this->CANDIDATE->location != null;
        $hasProfession = count($this->CANDIDATE->vacancies) > 0;

        $text = view('info',[
            'lang' => $this->CANDIDATE->lang,
            'candidate' => $this->CANDIDATE,
        ])->render();

        $buttons = [
            [$this->button(view('btn.name',['hasName' => $hasName,'lang' =>$this->CANDIDATE->lang])->render(),['step' => 'set.name'])],
            [$this->button(view('btn.date',['hasDate' => $hasDate,'lang' =>$this->CANDIDATE->lang])->render(),['step' => 'set.date'])],
            [$this->button(view('btn.phone',['hasPhone' => $hasPhone,'lang' =>$this->CANDIDATE->lang])->render(),['step' => 'set.phone'])],
            [$this->button(view('btn.location',['hasLocation' => $hasLocation,'lang' =>$this->CANDIDATE->lang])->render(),['step' => 'set.location'])],
            [$this->button(view('btn.idnp',['hasIDNP' => $hasIDNP,'lang' =>$this->CANDIDATE->lang])->render(),['step' => 'set.idnp'])],
            [$this->button(view('btn.vacancy',['hasVacancy' => $hasProfession,'lang' =>$this->CANDIDATE->lang])->render(),['step' => 'vacancies.list', 'page' => 1])]
        ];

        if($hasName && $hasDate && $hasPhone && $hasIDNP && $hasProfession && $hasLocation) {
            $buttons[] =  [$this->button(view('btn.send',['lang' =>$this->CANDIDATE->lang])->render(),['step' => 'send1C'])];
        }

        $this->sendMessage($this->CANDIDATE->chat_id,$text, $buttons);
    }

    /** -----------#SEND_1C-------- **/
    public function send1c(){
        $this->CANDIDATE->current_step = 'send.1C';
        $this->CANDIDATE->sendData = true;
        $this->CANDIDATE->save();

        $url = 'http://10.10.10.64:7766/erpbuh30/odata/standard.odata/InformationRegister_ex_%D0%9A%D0%B0%D0%BD%D0%B4%D0%B8%D0%B4%D0%B0%D1%82%D1%8B%D0%9D%D0%B0%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D1%83?&$format=json';


        $error = false;
        foreach ($this->CANDIDATE->vacancies as $vacancy) {

            $param = [
                'IDNP' => $this->CANDIDATE->IDNP,
                "ЧатИД" => $this->CANDIDATE->chat_id,
                'ФИО' => $this->CANDIDATE->name,
                'Пол' => '',
                'ДатаРождения' => gmdate("Y-m-d", strtotime($this->CANDIDATE->date)),//1977-01-01T00:00:00
                'Должность_Key' => $vacancy->job_с_id,
                'Вакансия_Key' => $vacancy->ref_key,
                'Локации' => '',
                "АдресПроживания" => "",
                'Телефон' => $this->CANDIDATE->phone,
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

        $this->sendHR($error);
        $this->thanks();
    }

    /** -----------#SEND_HR-------- **/
    public function sendHR($error = false){
        $text = view('send_hr',['error' => $error, 'candidate' => $this->CANDIDATE, 'lang' => $this->CANDIDATE->lang,])->render();

        $this->sendMessage($this->GROUP_ID,$text);
    }

    /** -----------#THANKS-------- **/
    public function thanks(){
        $this->sendMessage($this->CANDIDATE->chat_id,__('trans.thanks',[],$this->CANDIDATE->lang));
    }

    /** -----------#DOCUMENTS-------- **/
    public function documents(){
        $documents = $this->CANDIDATE->documentsList();

        $text = view('documents',['documents' => $documents, 'lang' => $this->CANDIDATE->lang,])->render();

        $buttons = [];

        if(count($documents) > 0) {
            foreach($documents as $document){
                $btn_text = view('btn.document',['document' => $document, 'lang' => $this->CANDIDATE->lang,])->render();

                $buttons[] = [$this->button($btn_text,['step' => 'document.upload','id' => $document['id'],'vacancy_id' => $document['id']])];
            }

            $count = $this->CANDIDATE->documents()->whereNull('src')->where('required',true)->count();

            if($count == 0){
                $buttons[] = [$this->button(view('btn.send',['lang' =>$this->CANDIDATE->lang])->render(),['step' => 'send_docs'])];
            }
        }

        $this->sendMessage($this->CANDIDATE->chat_id,$text,$buttons);
    }

    /** -----------#DOCUMENTS_LIST-------- **/
    public function documentsList(){
        $this->CANDIDATE->current_step = $this->DATA['step'];
        $this->CANDIDATE->save();
        $this->documents();
    }

    /** -----------#SET_DOCUMENTS-------- **/
    public function setDocument(){
        $this->CANDIDATE->current_step = "document|".$this->DATA['id'];
        $this->CANDIDATE->save();
        $this->uploadDocument($this->DATA['id']);
    }

    /** -----------#GET_PHOTO-------- **/
    public function getPhoto(){
        $id = explode('|', $this->CANDIDATE->current_step)[1];
        $document = Document::find($id);

        if (isset($this->PHOTO) && $document) {
            $name = $this->CANDIDATE->IDNP . '/' . $document->name_ru . '.jpg';
            $src = $this->storePhoto($this->PHOTO['file_id'], $name);

            $this->CANDIDATE->documents()->updateExistingPivot($id, ['src' => $src, 'type' => 'image']);
        }

        $this->CANDIDATE->current_step = null;
        $this->CANDIDATE->save();

        $this->documents();

    }

    /** -----------#GET_DOCUMENT-------- **/
    public function getDocument(){
        $id = explode('|', $this->CANDIDATE->current_step)[1];
        $extArr = ['pdf','doc','docx'];

        if(!in_array($this->DOCUMENT['file_ext'], $extArr)){
            $this->uploadDocument($id,'format');
        }
        elseif($this->DOCUMENT['file_size'] > 20000000){
            $this->uploadDocument($id,'size');
        }
        else {

            $document = Document::find($id);

            if (isset($this->DOCUMENT) && $document) {
                $name = $this->CANDIDATE->IDNP . '/' . $document->name_ru . '.'.$this->DOCUMENT['file_ext'];
                $src = $this->storePhoto($this->DOCUMENT['file_id'], $name);

                $this->CANDIDATE->documents()->updateExistingPivot($id, ['src' => $src, 'type' => 'doc']);


            }

            $this->CANDIDATE->current_step = null;
            $this->CANDIDATE->save();
            $this->documents();
        }


    }

    /** -----------#STORE_PHOTO-------- **/
    public function storePhoto($photo, $name){
        $file = $this->getFile($photo);
        $path = null;
        if($file['code'] === 200){
            $path = '/images/'.$name;
            Storage::disk('public')->put($path, $file['file']);
        }
        return $path;
    }

    /** -----------#SEND_DOCS-------- **/
    public function sendDocs(){
        $this->CANDIDATE->current_step = $this->DATA['step'];
        $this->CANDIDATE->sendDocs = true;
        $this->CANDIDATE->save();
        $text = view('sendDocs',['candidate' => $this->CANDIDATE, 'lang' => $this->CANDIDATE->lang,])->render();

        $this->sendMessage($this->GROUP_ID,$text);


        foreach($this->CANDIDATE->documentsList() as $document) {

            if($document->pivot->type == 'image'){
                $this->sendPhoto($this->GROUP_ID,$document->pivot->src,$document['name_'.$this->CANDIDATE->lang]);
            }
            if($document->pivot->type == 'doc'){
                $this->sendDocument($this->GROUP_ID,$document->pivot->src,$document['name_'.$this->CANDIDATE->lang]);
            }
        }


        $this->thanks();
        $this->sendToFileStore();
    }

    function  uploadDocument($doc_id ,$error = false){
        $doc_name = Document::find($doc_id)['name_'.$this->CANDIDATE->lang];

        $text = view('attach_doc',['doc_name' => $doc_name, 'lang' => $this->CANDIDATE->lang, 'error' => $error])->render();

        $buttons = [[ $this->button(view('btn.undo',['lang' => $this->CANDIDATE->lang])->render(),['step' => 'documents'])]];

        $this->sendMessage($this->CANDIDATE->chat_id,$text,$buttons);
    }

    function validateNumber($number) {
        if ( substr($number, 0, 2) !== "09" && substr($number, 0, 2) !== "20") {
            return false;
        }

        $multipliers = [7, 3, 1, 7, 3, 1, 7, 3, 1, 7, 3, 1];

        $weightedSum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = intval($number[$i]);
            $weightedSum += $digit * $multipliers[$i];
        }

        $calculatedControlDigit = $weightedSum % 10;
        $providedControlDigit = intval($number[12]);

        if ($calculatedControlDigit !== $providedControlDigit) {
            return false;
        }

        return true;
    }

    function sendToFileStore(){
        $this->makeDir($this->CANDIDATE->IDNP);
        foreach ($this->CANDIDATE->documents as $document){
            if($document->pivot->src !== null){
                $this->uploadFile($this->CANDIDATE->IDNP,$document->pivot->src);
            }
        }

    }
}
