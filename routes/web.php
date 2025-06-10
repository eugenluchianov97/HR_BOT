<?php

use App\Models\CandidateDocuments;
use App\Models\Day;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::group(['prefix' => 'debug'], function(){


    Route::get('/clear-me', function (){
        $emploe = \App\Models\Candidate::where('chat_id',867008520)->first();
        $emploe->sendDocs = 0;
        $emploe->save();
    });

    Route::get('/delete-me', function (){
        $emploe = \App\Models\Candidate::where('chat_id',867008520)->first();

        if($emploe){
            $emploe->delete();
        }

    });

    Route::get('/users', function (){
        $candidates = \App\Models\Candidate::with('documents')
            ->with('vacancies')
            ->get();
        dd($candidates);
    });
    Route::get('/documents', function (){
        $documents = \App\Models\Document::all();
        dump($documents);
    });

    Route::get('/vacancies', function (){
        $documents = \App\Models\Vacancy::all();
        dump($documents);
    });

    Route::get('/user', function (){
        $candidate = \App\Models\Candidate::where('chat_id',867008520)
            ->with('documents')
            ->with('vacancies')
            ->first();


        dump($candidate);

    });


});

/**----------------------------------------------------**/

Route::get('/migrate', function (){
    Artisan::call('migrate');
});

Route::get('/migrate-refresh', function (){
    Artisan::call('db:wipe');
});

Route::group(['prefix' => 'telegram'], function(){
    Route::post('/response/0123456789',\App\Http\Controllers\TelegrammController::class);
    Route::get('/setWebhook', [\App\Http\Controllers\TelegrammController::class,'setWebhook']);
    Route::get('/deleteWebhook', [\App\Http\Controllers\TelegrammController::class,'deleteWebhook']);
    Route::get('/infoWebhook', [\App\Http\Controllers\TelegrammController::class,'webhookInfo']);
    Route::post('/callback', [\App\Http\Controllers\TelegrammController::class,'callback']);


    Route::get('/sync', [\App\Http\Controllers\TelegrammController::class,'sync']);
});
