<?php


namespace App\Services;

use Illuminate\Support\Str;
use Exception;


class SmsService
{
    public static function sendsms($phones, $code = false)
    {
        $result_send = [
            'suc' => 1,
            'text' => '',
            'id_sms' => null
        ];
        if(env('APP_NAME')=='Laravel_test'){
            return [
                'suc' => 1,
                'text' => '',
                'id_sms' => time()
            ];
        }
        try {
            // Подключаемся к серверу
            $client = new \SoapClient('http://turbosms.in.ua/api/wsdl.html');
            // Данные авторизации
            $login = env('TURBOSMS_LOGIN');
            $password = env('TURBOSMS_PASWORD');
            $auth = [
                'login' => $login,
                'password' => $password
            ];
            $result = $client->Auth($auth);

            $result = $client->GetCreditBalance();
            if ((int)$result->GetCreditBalanceResult < 1) {
                $result_send = [
                    'suc' => false,
                    'text' => 'Будь ласка, зв\'яжіться з Bb Crm',
                    'id_sms' => null
                ];
                return $result_send;
            }
            if (!$code) {
                $code = rand(1000, 9999);
            }
            $sms = [
                'sender' => 'BbCrm',
                'destination' => $phones,
                'text' => $code
            ];
            $result = $client->SendSMS($sms);
            if (gettype($result->SendSMSResult->ResultArray) == "string") {
                $result_send = [
                    'suc' => false,
                    'text' => $result->SendSMSResult->ResultArray,
                    'id_sms' => null
                ];
                return $result_send;
            }
            if ($result->SendSMSResult->ResultArray[0] != "Сообщения успешно отправлены") {
                $result_send = [
                    'suc' => false,
                    'text' => $result->SendSMSResult->ResultArray[0] . ' ' . $result->SendSMSResult->ResultArray[1],
                    'id_sms' => null
                ];
                return $result_send;
            }

            $id_sms = $result->SendSMSResult->ResultArray[1];

            $result_send = [
                'suc' => 1,
                'text' => '',
                'id_sms' => $id_sms
            ];
            return $result_send;
        } catch (Exception $e) {
            $text = $e->getMessage();
            $result_send = [
                'suc' => false,
                'text' => $text,
                'id_sms' => null
            ];
            return $result_send;
        }

    }


    /*
     *  USA
     */
    public static function sendsmsUsa($phone, $code = false){
        $result_send = [
            'suc' => 1,
            'text' => '',
            'id_sms' => null
        ];
        $conf=config('sms');
        if (!$code) {
            $code = rand(1000, 9999);
        }
        try{
            $client = new \Twilio\Rest\Client($conf['twillo_sid'], $conf['twillo_token']);
            $client->messages->create(
                $phone,
                array(
                   'from' => '+18337591488',
                    'body' =>$code
                )
            );

            $id_sms = Str::uuid();
            $result_send = [
                'suc' => 1,
                'text' => '',
                'id_sms' => $id_sms
            ];
            return $result_send;
        }catch (Exception $e) {
            $text = $e->getMessage();
            $result_send = [
                'suc' => false,
                'text' => $text,
                'id_sms' => null
            ];
            return $result_send;
        }

    }

    public static function validatePhoneUsa($phone) {
        $firstCharacter = substr($phone, 0, 2);
        if($firstCharacter=='+1')
            return true;
        else
            return false;
    }
}
