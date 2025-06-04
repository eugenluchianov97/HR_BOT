<?php

use App\Models\CandidateDocuments;
use App\Models\CandidateVacancies;
use App\Models\Day;
use App\Models\Vacancy;
use App\Models\VacancyDays;
use App\Models\VacancyDocuments;
use App\Models\VacancyRequirements;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::group(['prefix' => 'debug'], function(){
    Route::get('/dir', [\App\Http\Services\TelegramService::class,'makeDir']);
    Route::get('/lang', function(){
        dump(__('trans.test',['from' =>10, 'to' => 20],'ru'));
    });

    Route::get('/file', [\App\Http\Services\TelegramService::class,'uploadFile']);



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
        $documents = \App\Models\Document::with('vacancies')->get();
        dump($documents);
    });

    Route::get('/vacancies', function (){
        $documents = \App\Models\Vacancy::all();
        dump($documents);
    });

    Route::get('/vacancies/paginate', function (){
        $documents = \App\Models\Vacancy::paginate(2);
        dump($documents->items());
        dump('last-page = '.$documents->lastPage());
        dump('current-page = '.$documents->currentPage());

    });

    Route::get('/user', function (){
        $candidate = \App\Models\Candidate::where('chat_id',867008520)
            ->with('documents')
            ->with('vacancies')
            ->first();


        dump($candidate);

    });

    Route::get('/user-docs', function (){
        $candidate = \App\Models\Candidate::where('chat_id',867008520)
            ->with('documents')
            ->with('vacancies')
            ->first();

        dump($candidate->documentsList());


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
    Route::get('/setWebhook', [\App\Http\Services\TelegramService::class,'setWebhook']);
    Route::get('/deleteWebhook', [\App\Http\Services\TelegramService::class,'deleteWebhook']);
    Route::get('/infoWebhook', [\App\Http\Services\TelegramService::class,'webhookInfo']);
    Route::post('/callback', [\App\Http\Services\TelegramService::class,'callback']);
    Route::get('/vacancies/{skip}', [\App\Http\Services\TelegramService::class,'vacancies']);
    Route::get('/vacancy/{id}', [\App\Http\Services\TelegramService::class,'getVacancy']);

    Route::get('/sync', [\App\Http\Services\TelegramService::class,'sync']);
});
