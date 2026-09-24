<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    private function apiUrl(string $path): string
    {
        return rtrim(config('app.api_url'), '/') . '/' . ltrim($path, '/');
    }

    public function getSettingsClub(Request $request)
    {
        $route = $request->route;
        $api_key = config('app.api_key');
        $response = Http::withHeaders([
            'API-Key' => $api_key,
            'Accept' => 'application/json'
        ])->get($this->apiUrl('api/getSettingsClub'), [
            'route' => $route,
        ]);
        $res = $response->json();
        return response()->json($res);
    }

    public function getTablesBookings(Request $request)
    {
        $route = $request->route;
        $api_key = config('app.api_key');
        $response = Http::withHeaders([
            'API-Key' => $api_key,
            'Accept' => 'application/json'
        ])->get($this->apiUrl('api/getTablesBookings'), [
            'route' => $route,
            'date_booking'=>$request->date_booking
        ]);
        $res = $response->json();
        return response()->json($res);
    }

    public function getBokingsDops(Request $request)
    {
        $route = $request->route;
        $api_key = config('app.api_key');
        $response = Http::withHeaders([
            'API-Key' => $api_key,
            'Accept' => 'application/json'
        ])->get($this->apiUrl('api/getBokingsDops'), [
            'route' => $route,
            'date_booking' => $request->date_booking,
            'table_dop_id' => $request->table_dop_id,
        ]);
        $res = $response->json();
        return response()->json($res);
    }
    public function getTotalBookings(Request $request)
    {
        $route = $request->route;
        $api_key = config('app.api_key');
        $response = Http::withHeaders([
            'API-Key' => $api_key,
            'Accept' => 'application/json'
        ])->post($this->apiUrl('api/getTotalBookings'), [
            'route' => $route,
            'orders' => $request->orders,
        ]);
        $res = $response->json();
        return response()->json($res);
    }

    public function addBooking(Request $request)
    {
        $route = $request->route;
        $api_key = config('app.api_key');
        $response = Http::withHeaders([
            'API-Key' => $api_key,
            'Accept' => 'application/json'
        ])->post($this->apiUrl('api/addBooking'), [
            'route' => $route,
            'orders' => $request->orders,
            'phone'=>$request->phone,
        ]);
        $res = $response->json();
        return response()->json($res);
    }
    public function payBooking(Request $request)
    {
        $route = $request->route;
        $api_key = config('app.api_key');
        $response = Http::withHeaders([
            'API-Key' => $api_key,
            'Accept' => 'application/json'
        ])->post($this->apiUrl('api/payBooking'), [
            'route' => $route,
            'orders' => $request->orders,
            'phone'=>$request->phone,
            'type_pay'=>$request->type_pay,
        ]);
        $res = $response->json();
        return response()->json($res);
    }
    public function bookings(Request $request)
    {
        $route = $request->route;
        $api_key = config('app.api_key');
        $response = Http::withHeaders([
            'API-Key' => $api_key,
            'Accept' => 'application/json'
        ])->get($this->apiUrl('api/bookings'), [
            'route' => $route,
            'phone'=>$request->phone,
        ]);
        $res = $response->json();
        return response()->json($res);
    }
    public function removeBooking(Request $request)
    {
        $route = $request->route;
        $api_key = config('app.api_key');
        $response = Http::withHeaders([
            'API-Key' => $api_key,
            'Accept' => 'application/json'
        ])->post($this->apiUrl('api/removeBooking'), [
            'route' => $route,
            'phone'=>$request->phone,
            'booking_id'=>$request->booking_id,
            /*
             *                                 phone:state.phone,
                                booking_id:booking_id
             */
        ]);
        $res = $response->json();
        return response()->json($res);
    }

    public function updateClearOrder(Request $request)
    {
        $route = $request->route;
        $api_key = config('app.api_key');
        $response = Http::withHeaders([
            'API-Key' => $api_key,
            'Accept' => 'application/json'
        ])->post($this->apiUrl('api/updateClearOrder'), [
            'route' => $route,
            'phone'=>$request->phone,
            /*
             *                                 route: state.route,
                                phone:state.phone,
             */
        ]);
        $res = $response->json();
        return response()->json($res);
    }

    public function getCheck(Request $request)
    {
        $route = $request->route;
        $api_key = config('app.api_key');
        $response = Http::withHeaders([
            'API-Key' => $api_key,
            'Accept' => 'application/json'
        ])->post($this->apiUrl('api/getCheck'), [
            'route' => $route,
            'pay_id'=>$request->pay_id,
            /*
             *                     route: state.route,
                    pay_id:state.pay_id
             */
        ]);
        $res = $response->json();
        return response()->json($res);
    }




}
