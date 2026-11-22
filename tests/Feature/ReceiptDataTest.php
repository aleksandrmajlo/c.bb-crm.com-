<?php

namespace Tests\Feature;

use App\Services\Receipts\ReceiptData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ReceiptDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.receipt_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        $schema = Schema::connection('receipt_test');
        $schema->create('orders', function (Blueprint $t) { $t->id(); $t->integer('booking_id')->nullable(); $t->integer('table_id')->nullable(); $t->integer('employment_id')->nullable(); $t->integer('cashbox_id')->nullable(); $t->string('employment_title_snapshot')->nullable(); $t->string('trainer_name_snapshot')->nullable(); $t->decimal('amount'); $t->boolean('type_bar'); $t->boolean('type_billiard'); $t->timestamp('start')->nullable(); $t->timestamp('closed')->nullable(); });
        $schema->create('settings', function (Blueprint $t) { $t->string('type'); $t->string('setting')->nullable(); });
        $schema->create('products', function (Blueprint $t) { $t->id(); $t->string('title'); $t->softDeletes(); });
        $schema->create('order_product', function (Blueprint $t) { $t->integer('order_id'); $t->integer('product_id'); $t->decimal('count'); $t->decimal('price'); });
        $schema->create('tables', function (Blueprint $t) { $t->id(); $t->integer('number'); $t->string('type'); $t->softDeletes(); });
        $schema->create('bookings', function (Blueprint $t) { $t->id(); $t->integer('employment_id')->nullable(); $t->integer('trainer_id')->nullable(); });
        $schema->create('cashboxes', function (Blueprint $t) { $t->id(); $t->string('title'); $t->softDeletes(); });
        $schema->create('employments', function (Blueprint $t) { $t->id(); $t->string('title'); $t->softDeletes(); });
        $schema->create('users', function (Blueprint $t) { $t->id(); $t->string('name'); $t->softDeletes(); });
        $schema->create('coupons', function (Blueprint $t) { $t->id(); $t->integer('type'); $t->integer('sum')->nullable(); $t->integer('applying')->nullable(); });
        $schema->create('couponlogs', function (Blueprint $t) { $t->integer('order_id'); $t->integer('coupon_id'); $t->decimal('sum')->nullable(); $t->decimal('sum_sk')->nullable(); });
        $schema->create('order_receipt_signatures', function (Blueprint $t) { $t->integer('order_id'); $t->string('signature_hash'); });
        $db = DB::connection('receipt_test');
        $db->table('orders')->insert(['id' => 1, 'type_bar' => 1, 'type_billiard' => 0, 'amount' => 142.5, 'closed' => '2026-11-21 12:00:00']);
        $db->table('settings')->insert([['type' => 'valuta', 'setting' => '₴'], ['type' => 'club_name', 'setting' => 'УТЦ']]);
        $db->table('products')->insert(['id' => 1, 'title' => 'Намотка', 'deleted_at' => '2026-11-21 12:00:00']);
        $db->table('order_product')->insert(['order_id' => 1, 'product_id' => 1, 'count' => 1, 'price' => 150]);
    }

    public function test_receipt_data_includes_deleted_products_and_current_amount(): void
    {
        $data = app(ReceiptData::class)->get(DB::connection('receipt_test'), 1);
        $this->assertSame('УТЦ', $data['clubName']);
        $this->assertSame('грн', $data['currency']);
        $this->assertSame(142.5, $data['amount']);
        $this->assertSame('Намотка', $data['products']->first()->title);
        $this->assertSame('bar', $data['receiptType']);
    }

    public function test_closed_tennis_order_uses_recorded_payment_and_historical_names(): void
    {
        $db = DB::connection('receipt_test');
        $db->table('orders')->where('id', 1)->update(['booking_id' => 1, 'table_id' => 1, 'cashbox_id' => 1, 'type_bar' => 0, 'type_billiard' => 1, 'employment_title_snapshot' => 'Заняття', 'trainer_name_snapshot' => 'Тренер']);
        $db->table('tables')->insert(['id' => 1, 'number' => 3, 'type' => 'hard', 'deleted_at' => '2026-11-21 12:00:00']);
        $db->table('bookings')->insert(['id' => 1]);
        $db->table('cashboxes')->insert([['id' => 1, 'title' => 'Готівкою'], ['id' => 2, 'title' => 'Карткою']]);
        Schema::connection('receipt_test')->create('tennis_order_split_payments', function (Blueprint $t) { $t->integer('order_id'); $t->decimal('primary_amount'); $t->decimal('secondary_amount'); $t->string('secondary_type'); $t->integer('secondary_cashbox_id')->nullable(); });
        $db->table('tennis_order_split_payments')->insert(['order_id' => 1, 'primary_amount' => 50, 'secondary_amount' => 40, 'secondary_type' => 'cashbox', 'secondary_cashbox_id' => 2]);
        $data = app(ReceiptData::class)->get($db, 1);
        $this->assertSame(90.0, $data['amount']);
        $this->assertSame('Готівкою + Карткою', $data['cashboxName']);
        $this->assertSame('№3 Хард', $data['tableName']);
        $this->assertSame('Заняття', $data['activity']);
        $this->assertSame('Тренер', $data['trainer']);
        $this->assertSame('tennis', $data['receiptType']);
    }

    public function test_open_orders_are_not_available(): void
    {
        DB::connection('receipt_test')->table('orders')->where('id', 1)->update(['closed' => null]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(ReceiptData::class)->get(DB::connection('receipt_test'), 1);
    }

    public function test_signed_receipt_and_pdf_use_the_requested_database_without_changing_mysql(): void
    {
        $tenant = 'receipt_test_'.bin2hex(random_bytes(6));
        $databaseFile = base_path($tenant);
        $originalMysql = config('database.connections.mysql');
        $url = URL::signedRoute('order-receipt', ['tenant' => $tenant, 'orderId' => 1], absolute: false);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        DB::connection('receipt_test')->table('order_receipt_signatures')->insert(['order_id' => 1, 'signature_hash' => hash('sha256', $query['signature'])]);
        DB::connection('receipt_test')->statement("VACUUM INTO '".$databaseFile."'");
        config(['database.connections.mysql' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::purge('mysql');

        try {
            $response = $this->get($url)->assertOk()->assertSee('Намотка')->assertSee('142.50 грн');
            $response->assertHeader('Cache-Control', 'no-store, private');
            $this->get(str_replace('/1?', '/2?', $url))->assertForbidden();
            $this->get(URL::signedRoute('order-receipt', ['tenant' => $tenant, 'orderId' => 2], absolute: false))->assertForbidden();
            $this->get($url.'&download=pdf')->assertForbidden();
            preg_match('/href="([^" ]*download=pdf[^" ]*)"/', $response->getContent(), $matches);
            $this->assertArrayHasKey(1, $matches);
            $pdfUrl = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $pdf = $this->get($pdfUrl)->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $pdf->getContent());
            $this->assertSame(':memory:', config('database.connections.mysql.database'));
            $this->assertNull(config('database.connections.order_receipt'));
        } finally {
            DB::purge('mysql');
            config(['database.connections.mysql' => $originalMysql]);
            @unlink($databaseFile);
        }
    }

    public function test_token_receipt_uses_shared_registry_and_keeps_old_links_working(): void
    {
        $tenant = 'receipt_test_'.bin2hex(random_bytes(6));
        $databaseFile = base_path($tenant);
        $originalMysql = config('database.connections.mysql');
        $originalBillPay = config('database.connections.bill_pay');
        $token = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
        $legacyUrl = URL::signedRoute('order-receipt', ['tenant' => $tenant, 'orderId' => 1], absolute: false);
        parse_str((string) parse_url($legacyUrl, PHP_URL_QUERY), $legacyQuery);
        DB::connection('receipt_test')->table('order_receipt_signatures')->insert(['order_id' => 1, 'signature_hash' => hash('sha256', $legacyQuery['signature'])]);
        DB::connection('receipt_test')->statement("VACUUM INTO '".$databaseFile."'");
        config([
            'database.connections.mysql' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'database.connections.bill_pay' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ]);
        DB::purge('mysql');
        DB::purge('bill_pay');
        $schema = Schema::connection('bill_pay');
        $schema->create('billiards', function (Blueprint $t) { $t->id(); $t->string('idd'); });
        $schema->create('order_receipt_links', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('billiard_id'); $t->unsignedBigInteger('order_id'); $t->char('token_hash', 64)->unique(); });
        DB::connection('bill_pay')->table('billiards')->insert(['id' => 14, 'idd' => $tenant]);
        DB::connection('bill_pay')->table('order_receipt_links')->insert(['billiard_id' => 14, 'order_id' => 1, 'token_hash' => hash('sha256', $token)]);

        try {
            $url = '/order-receipt/'.$token;
            $response = $this->get($url)->assertOk()->assertSee('Намотка')->assertSee('142.50 грн')->assertDontSee($tenant);
            $response->assertHeader('Cache-Control', 'no-store, private');
            $this->get('/order-receipt/'.str_repeat('A', 32))->assertForbidden();
            $this->get($url.'?unexpected=1')->assertForbidden();
            $this->get($url.'?download=other')->assertForbidden();
            $this->get($legacyUrl)->assertOk()->assertSee('Намотка');

            preg_match('/href="([^" ]*download=pdf[^" ]*)"/', $response->getContent(), $matches);
            $this->assertArrayHasKey(1, $matches);
            $pdfUrl = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $pdf = $this->get($pdfUrl)->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $pdf->getContent());
            $this->assertSame(':memory:', config('database.connections.mysql.database'));
            $this->assertNull(config('database.connections.order_receipt'));

            DB::connection('bill_pay')->table('order_receipt_links')->delete();
            $this->get($url)->assertForbidden();
        } finally {
            DB::purge('mysql');
            DB::purge('bill_pay');
            config(['database.connections.mysql' => $originalMysql, 'database.connections.bill_pay' => $originalBillPay]);
            @unlink($databaseFile);
        }
    }
}
