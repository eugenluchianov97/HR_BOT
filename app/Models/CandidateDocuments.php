<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateDocuments extends Model
{
    use HasFactory;

    protected $table = 'candidate_documents';
    protected $guarded = false;
}
