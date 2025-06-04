<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'requirements';

    public static function getName($ref_key,$lang = 'ru'){
        $item =  self::where('Ref_Key',$ref_key)->first();
        return $item != null ? $item['name_'.$lang] : '';
    }
}
