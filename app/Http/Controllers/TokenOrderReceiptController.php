<?php

namespace App\Http\Controllers;

use App\Services\Receipts\ReceiptData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TokenOrderReceiptController extends Controller
{
    public function __invoke(Request $request, string $token, ReceiptData $receiptData)
    {
        if (app()->bound('debugbar')) app('debugbar')->disable();
        $download = $request->query('download');
        if (array_diff(array_keys($request->query()), ['download']) || ($download !== null && $download !== 'pdf')) {
            return response('Посилання недійсне.', 403)->header('Cache-Control', 'no-store');
        }

        try {
            $link = DB::connection('bill_pay')->table('order_receipt_links as links')
                ->join('billiards', 'billiards.id', '=', 'links.billiard_id')
                ->where('links.token_hash', hash('sha256', $token))
                ->select('links.order_id', 'billiards.idd as tenant')
                ->first();
        } catch (QueryException $exception) {
            return response('Рахунок тимчасово недоступний.', 503)->header('Cache-Control', 'no-store');
        }
        if (!$link || !is_string($link->tenant) || !preg_match('/\A[A-Za-z0-9_-]+\z/', $link->tenant)) {
            return response('Посилання недійсне.', 403)->header('Cache-Control', 'no-store');
        }

        $connection = DB::connection('mysql')->getConfig();
        $connection['database'] = $link->tenant;
        unset($connection['url']);
        $previous = config('database.connections.order_receipt');
        config(['database.connections.order_receipt' => $connection]);
        DB::purge('order_receipt');

        try {
            $data = $receiptData->get(DB::connection('order_receipt'), (int) $link->order_id);
            $data['downloadUrl'] = route('order-receipt-token', ['token' => $token, 'download' => 'pdf'], absolute: false);
            $response = $download === 'pdf'
                ? Pdf::loadView('receipts.pdf', $data)->setPaper('a4')->download('receipt-'.$link->order_id.'.pdf')
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
