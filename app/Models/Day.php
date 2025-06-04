<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'days';

    public static function getName($ref_key,$lang = 'ru'){
        $item =  self::where('Ref_Key',$ref_key)->first();
        return $item != null ? $item['name_'.$lang] : '';
    }

    public function vacancies()
    {
        return $this->belongsToMany(Vacancy::class,'vacancies_days','vacancy_id','day_id');
    }

    public function vacancy(){

    }
}
