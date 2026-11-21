<?php

namespace App\Http\Controllers;

use App\Services\Receipts\ReceiptData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class OrderReceiptController extends Controller
{
    public function __invoke(Request $request, string $tenant, int $orderId, ReceiptData $receiptData)
    {
        if (app()->bound('debugbar')) app('debugbar')->disable();
        $signature = $request->query('signature');
        $download = $request->query('download');
        $query = $request->query();
        $allowedKeys = $download === 'pdf' ? ['signature', 'download', 'pdf_signature'] : ['signature'];
        if (!is_string($signature) || !preg_match('/\A[a-f0-9]{64}\z/', $signature)
            || array_diff(array_keys($query), $allowedKeys)
            || ($download !== null && $download !== 'pdf')
            || ($download === 'pdf' && (!is_string($request->query('pdf_signature'))
                || !hash_equals(hash_hmac('sha256', 'order-receipt-pdf', $signature), $request->query('pdf_signature'))))) {
            return response('Посилання недійсне.', 403)->header('Cache-Control', 'no-store');
        }

        // Use the existing MySQL credentials without changing the application's default connection.
        $connection = DB::connection('mysql')->getConfig();
        $connection['database'] = $tenant;
        unset($connection['url']);
        $previous = config('database.connections.order_receipt');
        config(['database.connections.order_receipt' => $connection]);
        DB::purge('order_receipt');

        try {
            try {
                $db = DB::connection('order_receipt');
                $valid = $db->table('order_receipt_signatures')->where('order_id', $orderId)
                    ->where('signature_hash', hash('sha256', $signature))->exists();
            } catch (QueryException $exception) {
                return response('Посилання недійсне.', 403)->header('Cache-Control', 'no-store');
            }
            if (!$valid) return response('Посилання недійсне.', 403)->header('Cache-Control', 'no-store');

            $data = $receiptData->get($db, $orderId);
            $data['downloadUrl'] = route('order-receipt', [
                'tenant' => $tenant, 'orderId' => $orderId, 'signature' => $signature,
                'download' => 'pdf', 'pdf_signature' => hash_hmac('sha256', 'order-receipt-pdf', $signature),
            ], absolute: false);

            $response = $request->query('download') === 'pdf'
                ? Pdf::loadView('receipts.pdf', $data)->setPaper('a4')->download('receipt-'.$orderId.'.pdf')
                : response()->view('receipts.order', $data);

            return $response->header('Cache-Control', 'private, no-store')
                ->header('Referrer-Policy', 'no-referrer')
                ->header('X-Robots-Tag', 'noindex, nofollow')
                ->header('Content-Security-Policy', "default-src 'none'; style-src 'unsafe-inline'; script-src 'self'; base-uri 'none'; frame-ancestors 'none'");
        } finally {
            DB::purge('order_receipt');
            config(['database.connections.order_receipt' => $previous]);
        }
    }
}
