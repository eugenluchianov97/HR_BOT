<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'documents';

    public static function getName($id,$lang = 'ru'){
        $item =  self::find($id);
        return $item != null ? $item['name_'.$lang] : '';
    }

    public function candidates()
    {
        return $this->belongsToMany(Candidate::class)->withPivot('require');
    }

}
