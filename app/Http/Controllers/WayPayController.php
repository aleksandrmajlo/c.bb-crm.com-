<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use WayForPay\SDK\Collection\ProductCollection;
use WayForPay\SDK\Credential\AccountSecretCredential;
use WayForPay\SDK\Credential\AccountSecretTestCredential;
use WayForPay\SDK\Domain\Client;
use WayForPay\SDK\Domain\Product;
use WayForPay\SDK\Wizard\PurchaseWizard;
use Illuminate\Support\Facades\Http;


class WayPayController extends Controller
{
    public function way(Request $request)
    {
        $pay_id = $request->pay_id;
        $route = $request->route;
        $first_name = $request->first_name;
        $last_name = $request->last_name;

        $key = env('API_KEY');
        $url_api = env('API_URL');

        Log::info('Way pay_id',['pay_id' => $pay_id,]);
        $url = $url_api . 'api/getWay';
        $response = Http::withHeaders([
            'API-Key' => $key,
        ])->post($url, [
            'pay_id' => $pay_id,
            'route' => $route,
            'first_name' => $first_name,
            'last_name' => $last_name,
        ]);
        $res = [
            'suc' => false,
        ];
         Log::info('Way $response',['response' => $response->json()]);
        if ($response->successful()) {
            $datas = $response->collect();
            $amout=floatval($datas->get('amout'));
            $client=$datas->get('client');
            $orders=$datas->get('orders');

            $orders_datas=[ new Product('test', 0.01, 1)];
            //$credential = new AccountSecretTestCredential();
            $credential = new AccountSecretCredential('c_bb_crm_com', '14765279d6972f5bd61b3caa324fc791435b51a7');
            $datas = PurchaseWizard::get($credential)
                ->setOrderReference($datas->get('order_id'))
                ->setAmount($amout)
                ->setCurrency('UAH')
                ->setOrderDate(new \DateTime())
                ->setMerchantDomainName('https://bb-crm.com')
                ->setClient(new Client(
                    $client['first_name'],
                    $client['last_name'],
                null,
                    $client['phone'],
                'UA'
                ))
                ->setProducts(new ProductCollection($orders_datas))
                ->setReturnUrl('https://p.bb-crm.com/way_success_atmosphera')
                ->setServiceUrl('https://p.bb-crm.com/way_callback_atmosphera')
                ->getForm()
                ->getData();
            $datas = array_filter($datas);
            Log::info('Way $datas',$datas);
            $res = [
                'suc' => true,
                'datas'=>$datas
            ];

        } else {

        }
        return response()->json($res);
    }

    public function way_success(Request $request){

    }
    public function way_callback(Request $request){

    }
}
