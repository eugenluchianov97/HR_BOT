<?php


namespace App\Http\Controllers\Telegram;


use App\Http\Traits\FileTransferTrait;
use App\Http\Traits\TelegramTrait;
use App\Models\Candidate;
use App\Models\CandidateDocuments;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentController
{
    use TelegramTrait;
    use FileTransferTrait;


    /** -----------#CANDIDATE_DOCUMENTS-------- **/
    public static function CandidateDocuments(Candidate $candidate,$documents){
        $candidate->access = true;
        $candidate->save();

        if(count($documents) > 0){
            CandidateDocuments::where('candidate_id',$candidate->id)->delete();
            foreach($documents as $document){
                $doc = Document::where('Ref_Key',$document['Ref_Key'])->first();

                CandidateDocuments::create([
                    'document_id' => $doc->id,
                    'candidate_id' => $candidate->id,
                    'required' =>$document['required']
                ]);
            }
        }

       self::documents($candidate);
    }

    /** -----------#DOCUMENTS-------- **/
    public static function documents(Candidate $candidate){
        $documents = $candidate->documents;

        $text = view('documents',['documents' => $documents, 'lang' => $candidate->lang,])->render();

        $buttons = [];

        if(count($documents) > 0) {
            foreach($documents as $document){
                $btn_text = view('btn.document',['document' => $document, 'lang' => $candidate->lang,])->render();

                $buttons[] = [self::button($btn_text,['step' => 'document.upload','id' => $document['id'],'vacancy_id' => $document['id']])];
            }

            $count = $candidate->documents()->whereNull('src')->where('required',true)->count();

            if($count == 0){
                $buttons[] = [self::button(view('btn.send',['lang' =>$candidate->lang])->render(),['step' => 'send_docs'])];
            }
        }

        self::sendMessage($candidate->chat_id,$text,$buttons);
    }

    /** -----------#DOCUMENTS_LIST-------- **/
    public static function documentsList(Candidate $candidate,$data){
        $candidate->current_step = $data['step'];
        $candidate->save();
        self::documents($candidate);
    }

    /** -----------#SET_DOCUMENTS-------- **/
    public static function setDocument(Candidate $candidate,$data){
        $candidate->current_step = "document|".$data['id'];
        $candidate->save();

        self::uploadDocument($candidate,$data['id']);
    }

    /** -----------#GET_PHOTO-------- **/
    public static function getPhoto(Candidate $candidate, $photo){
        $id = explode('|', $candidate->current_step)[1];
        $document = Document::find($id);

        if($photo['file_size'] > 20000000){
            self::uploadDocument($candidate,$id,'size');
        }
        else {
            if (isset($photo) && $document) {
                $name = $candidate->IDNP . '/' . $document->name_ru .'_'.$document->ref_key.'.jpg';
                $src = self::storePhoto($photo['file_id'], $name);

                $candidate->documents()->updateExistingPivot($id, ['src' => $src, 'type' => 'image']);
            }

            $candidate->current_step = null;
            $candidate->save();

            self::documents($candidate);
        }



    }

    /** -----------#GET_DOCUMENT-------- **/
    public static function getDocument(Candidate $candidate,$file){
        $id = explode('|', $candidate->current_step)[1];
        $extArr = ['pdf'];

        if(!in_array($file['file_ext'], $extArr)){
            self::uploadDocument($candidate,$id,'format');
        }
        elseif($file['file_size'] > 20000000){
            self::uploadDocument($candidate,$id,'size');
        }
        else {
            $document = Document::find($id);

            if (isset($file) && $document) {
                $name = $candidate->IDNP . '/' . $document->name_ru  .'_'.$document->ref_key.'.'.$file['file_ext'];
                $src = self::storePhoto($file['file_id'], $name);

                $candidate->documents()->updateExistingPivot($id, ['src' => $src, 'type' => 'doc']);
            }

            $candidate->current_step = null;
            $candidate->save();
            self::documents($candidate);
        }
    }


    /** -----------#SEND_DOCS-------- **/
    public static function sendDocs(Candidate $candidate, $data){
        $candidate->current_step = $data['step'];
        $candidate->sendDocs = true;
        $candidate->save();

        InfoController::thanks($candidate);

        $text = view('sendDocs',['candidate' => $candidate, 'lang' => $candidate->lang,])->render();

        self::sendMessage(InfoController::$GROUP_ID,$text);


        foreach($candidate->documents as $document) {

            if($document->pivot->type == 'image'){
                self::sendPhoto(InfoController::$GROUP_ID,$document->pivot->src,$document['name_'.$candidate->lang]);
            }
            if($document->pivot->type == 'doc'){
                self::sendDocument(InfoController::$GROUP_ID,$document->pivot->src,$document['name_'.$candidate->lang]);
            }
        }



        self::sendToFileStore($candidate);
    }

    public static function  uploadDocument(Candidate $candidate,$doc_id ,$error = false){
        $doc_name = Document::find($doc_id)['name_'.$candidate->lang];

        $text = view('attach_doc',['doc_name' => $doc_name, 'lang' => $candidate->lang, 'error' => $error])->render();

        $buttons = [[ self::button(view('btn.undo',['lang' => $candidate->lang])->render(),['step' => 'documents'])]];

        self::sendMessage($candidate->chat_id,$text,$buttons);
    }


    public static function storePhoto($photo, $name){
        $file = self::getFile($photo);
        $path = null;
        if($file['code'] === 200){
            $path = '/images/'.$name;
            Storage::disk('public')->put($path, $file['file']);
        }
        return $path;
    }


    public static function sendToFileStore(Candidate $candidate){
        self::makeDir($candidate->IDNP);
        foreach ($candidate->documents as $document){
            if($document->pivot->src !== null){
               self::uploadFile($candidate->IDNP,$document->pivot->src);
            }
        }

    }

}
