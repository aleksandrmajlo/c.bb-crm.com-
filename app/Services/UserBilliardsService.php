<?php


namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;


class UserBilliardsService
{
    public static function getBilliards($phone){
        $billiards = [];
        $bases = config('database.connections');
        foreach ($bases as $k => $basis) {
            if(isset($basis['isUser'])&&!$basis['isUser'])continue;
            if(isset($basis['isNot'])&&$basis['isNot'])continue;

            $user = DB::connection($k)->table('users')->select()->where('phone', $phone)->first();

            if ($user) {
                if($k=='bill_pay'){
                    $club_name = __('login.pay');
                }
                else{
                    $settings = DB::connection($k)->table('settings')->select()->where('type', 'club_name')->first();
                    if ($settings) {
                        $club_name = $settings->setting;
                    } else {
                        $club_name = 'Bb Crm';
                    }
                }
                $billiards[] = [
                    'user_id' => $user->id,
                    'billiards_id' => $basis['database'],
                    'club_name' => $club_name
                ];
            }
        }
        return $billiards;
    }

    public static function setAuth($billiard){
        $billiards_id = $billiard['billiards_id'];
        $user_id = $billiard['user_id'];
        // установка базы  данных
        $num_of_minutes_until_expire = 6000000;
        Cookie::queue('billiards_id', $billiards_id, $num_of_minutes_until_expire, null, env('SESSION_DOMAIN'));
        config(['database.connections.mysql.database' => $billiards_id]);
        DB::purge('mysql');
        DB::reconnect('mysql');

        Auth::loginUsingId($user_id, true);
    }

}
