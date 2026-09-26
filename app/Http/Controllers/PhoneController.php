<?php

namespace App\Http\Controllers;

use App\Models\Phone;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhoneController extends Controller
{
      public function sendPhone(Request $request){
          $phone=$request->phone;
          $route=$request->route;

          $digits = 6;
          $random_number = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
          if (app()->environment('local')) {
              $random_number = 111111;
              $id_sms = 'local-' . Str::uuid();
          } else {
              $validatePhoneUsa = SmsService::validatePhoneUsa($phone);
              if ($validatePhoneUsa) {
                  $result_send = SmsService::sendsmsUsa($phone, $random_number);
                  $id_sms = $result_send['id_sms'];
              } else {
                  $result_send = SmsService::sendsms($phone, $random_number);
                  $id_sms = $result_send['id_sms'];
              }
          }
          $condition = ['phone' => $phone];
          $values = [
              'sms_id' => $id_sms,
              'code' => $random_number,
              'route' => $route,
          ];
          Phone::updateOrCreate($condition, $values);
          return response()->json(
              [
                  'suc'=>true,
                  'sms_id' => $id_sms,
              ]
          );
      }
      public function sendCode(Request $request){
          $sms_id=$request->sms_id;
          $code=$request->code;
          $phone=Phone::where('sms_id',$sms_id)->first();
          if($phone->code==$code) {
              return response()->json(
                  [
                      'suc'=>true,
                  ]
              );
          }else{
              return response()->json(
                  [
                      'suc'=>false,
                  ]
              );
          }
      }
}
