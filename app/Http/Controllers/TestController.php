<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use WayForPay\SDK\Collection\ProductCollection;
use WayForPay\SDK\Credential\AccountSecretTestCredential;
use WayForPay\SDK\Domain\Client;
use WayForPay\SDK\Domain\Product;
use WayForPay\SDK\Wizard\PurchaseWizard;
use Illuminate\Support\Facades\Http;

class TestController extends Controller
{
    public function qr(){
        $qrCode = QrCode::size(200)->generate('https://example.com');
        return view('qr ', compact('qrCode'));
    }

    public function way_test(){


        $pay_id=1;
        $route='atmosphera';
        $last_name='1111';
        $first_name='22222';

        $key=env('API_KEY');
        $url_api=env('API_URL');

        $url=$url_api.'api/getWay';
        $response = Http::withHeaders([
            'API-Key' => $key,
        ])->post($url, [
            'pay_id' => $pay_id,
            'route' =>$route,
            'first_name'=>$first_name,
            'last_name'=>$last_name,
        ]);
        dump($response);
        if ($response->successful()) {
            $datas=$response->collect();
//            dd($datas);
            dd($datas);
        } else {

        }

        $credential = new AccountSecretTestCredential();
       //$credential = new AccountSecretCredential('account', 'secret');
//        dump($credential);
        $datas = PurchaseWizard::get($credential)
            ->setOrderReference(sha1(microtime(true)))
            ->setAmount(0.01)
            ->setCurrency('USD')
            ->setOrderDate(new \DateTime())
            ->setMerchantDomainName('https://google.com')
            ->setClient(new Client(
                'John',
                'Dou',
                'john.dou@gmail.com',
                '+12025550152',
                'USA'
            ))
            ->setProducts(new ProductCollection(array(
                new Product('test', 0.01, 1)
            )))
            ->setReturnUrl('http://localhost:8000/examples/returnUrl.php')
            ->setServiceUrl('http://localhost:8000/examples/serviceUrl.php')
            ->getForm()
            ->getData();
//            ->getWidget();

        $datas=array_filter($datas) ;
        return response()->json($datas);

        var_dump(array_filter(json_encode($datas)));
        exit();

        return view('test.way ', compact('widget'));

    }
}
