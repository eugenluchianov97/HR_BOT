<?php


namespace App\Http\Traits;


use CURLFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait FileTransferTrait
{


    public static $FILE_TRANSFER_URL = 'http://10.100.107.19:8080/api/v2/user';
    public static  $FILE_TRANSFER_USER = 'hrdocs';
    public static  $FILE_TRANSFER_PASS = 'N3PPyZ0JMiAesKS';

    public static function getToken(){
        $url = self::$FILE_TRANSFER_URL.'/token';
        $res  = Http::withBasicAuth(self::$FILE_TRANSFER_USER, self::$FILE_TRANSFER_PASS)->get($url);
        return [
            'code' => $res->status(),
            'token' => $res['access_token'] != null ? $res['access_token'] : ''
        ];
    }

    public static function makeDir($dirName = 'test'){
        $code =  500;
        $getToken = self::getToken();
        if($getToken['code'] == 200){
            $params = [
                'path' => 'hrdocs/'.$dirName,
                'mkdir_parents'=> false
            ];
            $url = self::$FILE_TRANSFER_URL.'/dirs';
            $res  = Http::withToken($getToken['token'])->withQueryParameters($params)->post($url);

            $code = $res->status();
        }

        return [
            'code' => $code
        ];
    }

    public static function uploadFile($dirName = 'test', $filePath = '/images/867008520/1671577c-1fb5-11f0-99e3-ad47a75ea4ce.jpg'){

        $code =  500;

        $getToken = self::getToken();
        if($getToken['code'] == 200){
            $url = self::$FILE_TRANSFER_URL.'/files';
            $params = [
                'path' => 'hrdocs/'.$dirName,
                'mkdir_parents'=> false
            ];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url.'?'.http_build_query($params),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST =>false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'filenames'=> new CURLFILE(storage_path($filePath)),
                ),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$getToken['token']
                ),
            ));

            curl_exec($curl);

            curl_close($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        }

        return [
            'code' => $code,
        ];
    }

}
