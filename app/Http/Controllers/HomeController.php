<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
//        return view('home');
    }

    public function atmosphera(Request $request)
    {
        $user = Auth::user();
        if ($user) {
        } else {
            $ip = $request->ip();
            $apiurl = "http://ip-api.com/php/";
            $ip_datas = null;
            try {
                $contents = file_get_contents($apiurl . $ip . '?fields=status,country,countryCode');
                $ip_datas = unserialize($contents);
                if ($ip_datas['status'] == "success") {
                    $num_of_minutes_until_expire = 60000;
                    Cookie::queue('billiards_countryCode', strtolower($ip_datas['countryCode']), $num_of_minutes_until_expire, null, env('SESSION_DOMAIN'));
                }
            } catch (\Exception $e) {

            }

            $title="Клуб Атмосфера - система бронювання";

            return view('home', [
                'route' => 'atmosphera',
                'ip_datas' => $ip_datas,
                'title'  =>$title
            ]);
        }

    }

    public function liodovii(Request $request)
    {
        $user = Auth::user();
        if ($user) {
        } else {
            $ip = $request->ip();
            $apiurl = "http://ip-api.com/php/";
            $ip_datas = null;
            try {
                $contents = file_get_contents($apiurl . $ip . '?fields=status,country,countryCode');
                $ip_datas = unserialize($contents);
                if ($ip_datas['status'] == "success") {
                    $num_of_minutes_until_expire = 60000;
                    Cookie::queue('billiards_countryCode', strtolower($ip_datas['countryCode']), $num_of_minutes_until_expire, null, env('SESSION_DOMAIN'));
                }
            } catch (\Exception $e) {

            }
            $title="Клуб Льодова арена - система бронювання";
            return view('home', [
                'route' => 'liodovii',
                'ip_datas' => $ip_datas,
                'title'  =>$title
            ]);
        }

    }

    public function rules_and_conditions(){
        $ip_datas=['status'=>'error'];
        $title='Правила і умови';
        return view('atmosphera.rules_and_conditions',compact('title','ip_datas'));
    }

    public function rules_for_turning(){
        $ip_datas=['status'=>'error'];
        $title='Правила повернення грошових коштів';
        return view('atmosphera.rules_for_turning',compact('title','ip_datas'));
    }

}
