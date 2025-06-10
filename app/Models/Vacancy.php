<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use function Laravel\Prompts\select;

class Vacancy extends Model
{
    use HasFactory;

    protected $guarded = false;

    protected $table = 'vacancies';


    public function days()
    {
        return $this->belongsToMany(Day::class, 'vacancy_days','vacancy_id','day_id')->withPivot('from', 'to');
    }

    public function requirements()
    {
        return $this->belongsToMany(Requirement::class, 'vacancy_requirements','vacancy_id','requirement_id')->withPivot('additional_info', 'necessarily');
    }

}
