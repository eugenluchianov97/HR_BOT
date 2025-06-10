<?php


namespace App\Http\Controllers\Telegram;


use App\Http\Traits\TelegramTrait;
use App\Models\Candidate;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Log;

class VacancyController
{
    use TelegramTrait;


    /**---------------#VACANCIES_LIST----------------------**/
    public static function vacancyList(Candidate $candidate, $page = 1){
        $candidate->current_step = 'vacancies.list';
        $candidate->save();

        $per_page = 10;

        $vacancies = Vacancy::where('status', 1)->paginate($per_page, ['*'], 'page', $page);

        $row1 = [];
        $row2 = [];
        $row3 = [];
        $row4 = [];

        $text = view('vacancies',[
            'vacancies' => $vacancies->items(),
            'lang' => $candidate->lang,
            'candidate' =>$candidate,
            'page' => $page,
            'per_page' => $per_page
        ])->render();

        if(count($vacancies) > 0) {
            $t = ceil(count($vacancies) / 2);
            foreach($vacancies->items() as $index => $vacancy) {
                $idx = (($page -1) * $per_page + $index)+1;
                $btn = self::button($idx,['step' => 'vacancy.set','page' => $page,'id' => $vacancy['id']]);

                if($index < $t) $row1[] = $btn;
                if($index >= $t) $row2[] = $btn;
            }
        }

        if($page > 1) $row3[] = self::button('⬅',['step' => 'vacancies.list','page' => $page-1]);

        if($page < $vacancies->lastPage()) $row3[] = self::button('➡',['step' => 'vacancies.list','page' => $page+1]);

        if(count($candidate->vacancies) > 0) $row4[] = self::button(__('trans.click_now',[],$candidate->lang),['step' => 'check.agree']);

        $buttons = [$row1, $row2, $row3,$row4];

        self::sendMessage($candidate->chat_id,$text, $buttons);
    }

    /**---------------#VACANCY----------------------**/
    public static function vacancy(Candidate $candidate,$id,$page = 1){
        $vacancy = Vacancy::find($id);
        if($vacancy){
            $text = view('vacancy',[
                'vacancy' => $vacancy,
                'has_vacancy' => $candidate->hasVacancy($vacancy->id),
                'lang' => $candidate->lang,
            ])->render();

            $row1 = [];
            $row2 = [];

            if($candidate->hasVacancy($vacancy->id)){
                $row1[] = self::button(__('trans.delete_vacancy',[],$candidate->lang),['step' => 'vacancy.delete','id' => $vacancy['id']]);
            }
            else {
                $row1[]  = self::button(__('trans.select_vacancy',[],$candidate->lang),['step' => 'vacancy.select','id' => $vacancy['id']]);
            }

            $row1[] = self::button(__('trans.back_to_list',[],$candidate->lang),['step' => 'vacancies.list','page' => $page]);

            if(count($candidate->vacancies) > 0){
                $row2[] = self::button(__('trans.click_now',[],$candidate->lang),['step' => 'check.agree']);
            }

            $buttons = [ $row1,$row2];

            self::sendMessage($candidate->chat_id,$text,$buttons);
        }
    }


    /**---------------#SET_VACANCY----------------------**/
    public static function setVacancy(Candidate $candidate,$data){
        $candidate->current_step = $data['step'];
        $candidate->save();
        self::vacancy($candidate,$data['id'], $data['page']);
    }

    /**---------------#SELECT_VACANCY----------------------**/
    public static function selectVacancy(Candidate $candidate,$data){
        $candidate->current_step = $data['step'];
        $candidate->save();

        $vacancy = Vacancy::find($data['id']);

        $candidate->vacancies()->attach($vacancy->id);
        $candidate->load('vacancies');

        self::vacancyList($candidate);
    }

    /**---------------#DELETE_VACANCY----------------------**/
    public static function deleteVacancy(Candidate $candidate,$data){
        $candidate->current_step = $data['step'];//vacancyItem
        $candidate->save();

        $vacancy = Vacancy::find($data['id']);

        $candidate->vacancies()->detach($vacancy->id);
        $candidate->load('vacancies');

        self::vacancyList($candidate);

    }

    public static function vacancyById(Candidate $candidate, $text){
        $ref = explode(' ', $text )[1];

        if(isset($ref)){

            $lang  = (isset(explode('_', $ref)[1]) && explode('_', $ref)[1] == 'ru') ? 'ru' : 'ro';
            $vacancy_key  =  explode('_', $ref)[0];

            $candidate->lang = $lang;
            $candidate->save();

            $vacancy = Vacancy::where('Ref_Key',$vacancy_key)->first();
            if($vacancy){
                self::vacancy($candidate,$vacancy->id);
            }
            else {
                self::vacancyList($candidate);
            }
        }
        else {
            self::sendMessage($candidate->chat_id,"Пусто = ".$text);
        }
    }

}
