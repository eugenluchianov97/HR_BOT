<?php


namespace App\Http\Traits;


use App\Models\Day;
use App\Models\Document;
use App\Models\Requirement;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Http;

trait Data1CTrait
{
    public $username = 'exchange';
    public $password = 'saturn';

    public function getDays(){
        try {
            $time_start = microtime(true);

            $url = 'http://10.10.10.64:7766/erpbuh30/odata/standard.odata/Catalog_ex_%D0%94%D0%BD%D0%B8%D0%9D%D0%B5%D0%B4%D0%B5%D0%BB%D0%B8?&$format=json';

            $res  = Http::withBasicAuth($this->username, $this->password)->get($url);

            if($res->status() == 200){
                $days = json_decode($res->body(),true)['value'];


                foreach($days as $day){
                    Day::firstOrCreate([
                        'ref_key' => $day['Ref_Key']
                    ],[
                        'ref_key' => $day['Ref_Key'],
                        'name_ru' => $day['Description'],
                        'name_ro' => $day['НаименованиеРум'],
                    ]);
                }
            }
            return 'Sync '.Day::count().' Days in '. (microtime(true) - $time_start).' sec.';
        }
        catch (\Exception $exception){
            return $exception->getMessage();
        }

    }

    public function getDocuments(){
        try {
            $time_start = microtime(true);

            $url = 'http://10.10.10.64:7766/erpbuh30/odata/standard.odata/Catalog_ex_%D0%94%D0%BE%D0%BA%D1%83%D0%BC%D0%B5%D0%BD%D1%82%D1%8B%D0%94%D0%BB%D1%8F%D0%A2%D1%80%D1%83%D0%B4%D0%BE%D1%83%D1%81%D1%82%D1%80%D0%BE%D0%B9%D1%81%D1%82%D0%B2%D0%B0?$format=json';

            $res  = Http::withBasicAuth($this->username, $this->password)->get($url);

            if($res->status() == 200){
                $documents = json_decode($res->body(),true)['value'];

                foreach($documents as $document){
                    Document::firstOrCreate([
                        'ref_key' => $document['Ref_Key']
                    ],[
                        'ref_key' => $document['Ref_Key'],
                        'name_ru' => $document['Description'],
                        'name_ro' => $document['НаименованиеРум'],

                    ]);
                }
            }

            return 'Sync '.Document::count().' Documents in '. (microtime(true) - $time_start).' sec.';

        }
        catch (\Exception $exception){
            return $exception->getMessage();
        }


    }

    public function getRequirements(){
        try {
            $time_start = microtime(true);
            $url = 'http://10.10.10.64:7766/erpbuh30/odata/standard.odata/Catalog_ex_%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F%D0%9A%D0%9A%D0%B0%D0%BD%D0%B4%D0%B8%D0%B4%D0%B0%D1%82%D1%83?$format=json';

            $res  = Http::withBasicAuth($this->username, $this->password)->get($url);

            if($res->status() == 200){
                $requirements = json_decode($res->body(),true)['value'];

                foreach($requirements as $requirement){
                    Requirement::firstOrCreate([
                        'ref_key' => $requirement['Ref_Key']
                    ],[
                        'ref_key' => $requirement['Ref_Key'],
                        'name_ru' => $requirement['Description'],
                        'name_ro' => $requirement['НаименованиеРум'],
                    ]);
                }
            }

            return 'Sync '.Requirement::count().' Requirements in '. (microtime(true) - $time_start).' sec.';
        }
        catch (\Exception $exception){
            return $exception->getMessage();
        }

    }


    public function getVacancies(){
        try {


        }
        catch (\Exception $exception){
            return $exception->getMessage();
        }

        $time_start = microtime(true);
        $url = 'http://10.10.10.64:7766/erpbuh30/odata/standard.odata/Catalog_ex_%D0%92%D0%B0%D0%BA%D0%B0%D0%BD%D1%81%D0%B8%D0%B8?&$format=json&$filter=%D0%A1%D1%82%D0%B0%D1%82%D1%83%D1%81%20eq%20%27%D0%9E%D1%82%D0%BA%D1%80%D1%8B%D1%82%D0%B0%27&$expand=*&$select=Ref_Key,%D0%A1%D1%82%D0%B0%D1%82%D1%83%D1%81,%D0%9F%D1%80%D0%B8%D0%BE%D1%80%D0%B8%D1%82%D0%B5%D1%82,%D0%94%D0%B0%D1%82%D0%B0%D0%9F%D1%83%D0%B1%D0%BB%D0%B8%D0%BA%D0%B0%D1%86%D0%B8%D0%B8,%D0%94%D0%BE%D0%BB%D0%B6%D0%BD%D0%BE%D1%81%D1%82%D1%8C/%D0%9D%D0%B0%D0%B8%D0%BC%D0%B5%D0%BD%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D0%B5%D0%9D%D0%B0%D0%A0%D1%83%D1%81%D1%81%D0%BA%D0%BE%D0%BC,%D0%94%D0%BE%D0%BB%D0%B6%D0%BD%D0%BE%D1%81%D1%82%D1%8C/Description,%D0%94%D0%BE%D0%BB%D0%B6%D0%BD%D0%BE%D1%81%D1%82%D1%8C/Ref_Key,%D0%9D%D0%B0%D1%81%D0%B5%D0%BB%D0%B5%D0%BD%D0%BD%D1%8B%D0%B9%D0%9F%D1%83%D0%BD%D0%BA%D1%82/Description,%D0%A0%D0%B0%D0%B9%D0%BE%D0%BD/Description,%D0%9C%D0%B8%D0%BD%D0%97%D0%B0%D1%80%D0%9F%D0%BB%D0%B0%D1%82%D0%B0,%D0%9C%D0%B0%D0%BA%D1%81%D0%97%D0%B0%D1%80%D0%9F%D0%BB%D0%B0%D1%82%D0%B0,%D0%93%D1%80%D0%B0%D1%84%D0%B8%D0%BA%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D1%8B,%D0%A0%D0%B0%D0%B1%D0%BE%D1%87%D0%B8%D0%B5%D0%94%D0%BD%D0%B8,%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F,%D0%94%D0%BE%D0%BA%D1%83%D0%BC%D0%B5%D0%BD%D1%82%D1%8B';

        $res  = Http::withBasicAuth($this->username, $this->password)->get($url);

        if($res->status() == 200) {
            $vacancies = json_decode($res->body(), true)['value'];
            foreach($vacancies as $vacancy){
                $vacancyItem = Vacancy::firstOrCreate([
                    'ref_key' => $vacancy['Ref_Key']
                ],[
                    'ref_key' => $vacancy['Ref_Key'],
                    'job_с_id' => $vacancy['Должность']['Ref_Key'],
                    'name_ru' => $vacancy['Должность']['НаименованиеНаРусском'],
                    'name_ro' => $vacancy['Должность']['Description'],
                    'payment_min' => $vacancy['МинЗарПлата'],
                    'payment_max' => $vacancy['МаксЗарПлата'],
                    'order' => $vacancy['Приоритет'],
                    'location_ro' => $vacancy['НаселенныйПункт']['Description'],
                    'location_ru' => $vacancy['НаселенныйПункт']['Description'],
                    'district_ro' => isset($vacancy['Район']) ? $vacancy['Район']['Description']:null,
                    'district_ru' => isset($vacancy['Район']) ? $vacancy['Район']['Description']:null,
                    'status' => $vacancy['Статус'] == "Открыта",

                ]);

                if(count($vacancy['РабочиеДни']) > 0){
                    $vacancyItem->days()->detach();
                    foreach($vacancy['РабочиеДни'] as $item){
                        $day = Day::where('Ref_Key',$item['ДеньНедели_Key'])->first();

                        if($day){
                            $vacancyItem->days()->attach($day->id, [
                                'from' => date('H:i', strtotime($item['НачалоДня'])),
                                'to' => date('H:i', strtotime($item['КонецДня']))
                            ]);
                        }

                    }
                }

                if(count($vacancy['Требования']) > 0){
                    $vacancyItem->requirements()->detach();

                    foreach($vacancy['Требования'] as $item){
                        $requirement = Requirement::where('Ref_Key',$item['Наименование_Key'])->first();
                        if($requirement){
                            $vacancyItem->requirements()->attach($requirement->id, [
                                    'additional_info' => $item['ДопСведения'],
                                    'necessarily' => $item['Условие']]
                            );
                        }

                    }




                }

            }
        }

        return 'Sync '.Vacancy::count().' Vacancies in '. (microtime(true) - $time_start).' sec.';




    }

    public function sync(){
        dump($this->getDays());
        dump($this->getDocuments());
        dump($this->getRequirements());
        dump($this->getVacancies());

    }

}
