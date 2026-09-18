<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PdfController extends Controller
{
    public function test(Request $request)
    {

        $id = rand(1, 1000);
        /*
         Получатель: ФОП Біленький Роман Зорянович
         Счет: UA473052990000026005026707459
         Код: 2822207315
         Сумма: 499
         */


        $tax_id = '2822207315';
        $iban = 'UA473052990000026005026707459';
        $recipient = 'ФОП Біленький Роман Зорянович ';
        $purpose = 'Рахунок 134';
        $amount = 499;


        /*

        Получатель: ФОП Біленький Роман Зорянович
        Счет: UA473052990000026005026707459
        Код: 2822207315
        Сумма: 499
        Назначение платежа: Рахунок 134

         */

        $qrData = "BCD\n001\n1\nUCT\n\n$recipient\n$iban\nUAH$amount\n$tax_id\n\n$purpose\n";
        $encodedData = base64_encode($qrData);
        $qrLink = "https://bank.gov.ua/qr/" . $encodedData;

        $linkOr = 'https://bank.gov.ua/qr/QkNECjAwMQoxClVDVAoK0KTQntCfINCR0ZbQu9C10L3RjNC60LjQuSDQoNC-0LzQsNC9INCX0L7RgNGP0L3QvtCy0LjRhwpVQTQ3MzA1Mjk5MDAwMDAyNjAwNTAyNjcwNzQ1OQpVQUg0OTkKMjgyMjIwNzMxNQoK0KDQsNGF0YPQvdC-0LogMTM0Cg==';

        /*
        echo '<pre>';
        var_dump($qrLink==$linkOr);
        echo '</pre>';

        echo '<pre>';
        var_dump($qrLink);
        echo '</pre>';
        echo '<pre>';
        var_dump($linkOr);
        echo '</pre>';
        exit();
        */

        $qrCode = base64_encode(QrCode::format('png')->size(200)->generate($qrLink));
//        $qrCode = base64_encode(QrCode::format('png')->size(200)->generate($linkOr));

        /*
        $qrData = "BCD\n001\n1\nUCT\n\n$fio\n$iban\nUAH$amount\n$code\n\n$purpose\n";
        $encodedData = base64_encode($qrData);
        $qrLink = "https://bank.gov.ua/qr/" . $encodedData;
        $qrCode = base64_encode(QrCode::format('svg')->size(200)->encoding('UTF-8')->generate($qrLink));
          */

        $date = Carbon::now();
        $newDate = $date->addDay();

        $data = [
            'qrCode' => $qrCode,
            'id' => $id,
            'title' => '',
            'date' => date('d.m.Y'),
            'end_pay' => $newDate->format('d.m.Y'),
            'code' => $tax_id,
            'numer_rach' => $iban,
            'recipient' => [
                'title' => $recipient,
                'phone' => '+380677755555'
            ],
            'payer' => [
                'title' => 'Name',
                'phone' => '+380677755555'
            ],
            'purpose_payment' => $purpose,
            'orders' => [

                [
                    'id' => 1,
                    'title' => '№4 Футбольне поле 25 вересня · 12:00 - 13:00',
                    'subtitle' => "оренда ракетки та м'ячів на годину за одиницю (100 грн.)",
                    'count' => 1,
                    'price' => 500,
                    'total' => 500
                ],
                [
                    'id' => 1,
                    'title' => '№4 Футбольне поле 25 вересня · 12:00 - 13:00',
                    'subtitle' => "оренда ракетки та м'ячів на годину за одиницю (100 грн.)",
                    'count' => 1,
                    'price' => 500,
                    'total' => 500
                ],
                [
                    'id' => 1,
                    'title' => '№4 Футбольне поле 25 вересня · 12:00 - 13:00',
                    'subtitle' => "оренда ракетки та м'ячів на годину за одиницю (100 грн.)",
                    'count' => 1,
                    'price' => 500,
                    'total' => 500
                ],
            ],
            'total' => $amount

        ];
        $pdf = Pdf::loadView('pdf.pay', $data);
        $pdf->setPaper('A4', 'portrait');
//        dd($pdf);
        return $pdf->stream('document.pdf');
        return $pdf->download('document.pdf');
    }

    public function pdf_donvald(Request $request)
    {
        $path = public_path('pdf');
        if (!\File::exists($path)) {
            \File::makeDirectory($path, 0755, true, true);
        }

        $url = $request->pdf_url_orig;

        $fileName =$request->pdf_name;

        $client = new Client();

        $response = $client->get($url);

        $fileContent = $response->getBody()->getContents();

        $publicPath = public_path('pdf/' . $fileName);
        \File::put($publicPath, $fileContent);
        $url = asset('pdf/' . $fileName);

        return response()->json([
            'pdf_url'=>$url,
            'success'=>true,
        ]);

    }

}
