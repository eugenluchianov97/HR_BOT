<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $table = 'candidates';
    protected $guarded = false;


    public function vacancies()
    {
        return $this->belongsToMany(Vacancy::class, 'candidate_vacancies','candidate_id','vacancy_id');
    }

    public function documents()
    {
        return $this->belongsToMany(Document::class, 'candidate_documents','candidate_id','document_id')->withPivot('vacancy_id','src', 'required','type');
    }

    public function hasVacancy($vacancy_id){
        $exists = false;
        foreach($this->vacancies as $candidate_vacancy){
            if($candidate_vacancy->id == $vacancy_id){
                $exists = true;
            }
        }

        return $exists;

    }

    public function hasDocument($document_id){
        $exists = false;
        foreach($this->documents as $document){
            if($document->id == $document_id){
                $exists =  true;
            }
        }

        return $exists;

    }

    public function documentsList(){
        $notUniqDocuments = [];
         foreach($this->documents as $document){
             if(!$document->pivot->required) {
                 $notUniqDocuments[$document->id] = $document;
             }
         }

        foreach($this->documents as $document){
            if($document->pivot->required) {
                $notUniqDocuments[$document->id] = $document;
            }
        }

         return $notUniqDocuments;
    }
}
