<?php

namespace App\Services\Receipts;

use Illuminate\Database\Connection;

class ReceiptData
{
    public function get(Connection $db, int $orderId): array
    {
        $order = $db->table('orders')->where('id', $orderId)
            ->where(fn ($query) => $query->where('type_bar', 1)->orWhere('type_billiard', 1))
            ->whereNotNull('closed')->first();
        abort_unless($order, 404);

        $products = $db->table('products')->join('order_product', 'products.id', '=', 'order_product.product_id')
            ->where('order_product.order_id', $orderId)
            ->select('products.title', 'order_product.count', 'order_product.price')->get()
            ->map(function ($product) {
                $product->pivot = (object) ['count' => $product->count, 'price' => $product->price];
                return $product;
            });

        $settings = $db->table('settings')->whereIn('type', ['valuta', 'club_name', 'working_mode', 'check_lang'])
            ->pluck('setting', 'type');
        $currency = $settings['valuta'] ?? 'грн';
        if (!$currency || $currency === '₴') $currency = 'грн';
        $clubName = $settings['club_name'] ?? null;
        $cashboxName = $order->cashbox_id ? $db->table('cashboxes')->where('id', $order->cashbox_id)->value('title') : null;
        $table = $order->table_id ? $db->table('tables')->where('id', $order->table_id)->first() : null;
        $tableName = $table ? $this->tableName($table, $settings['check_lang'] ?? 'ua') : null;
        $booking = $order->booking_id ? $db->table('bookings')->where('id', $order->booking_id)->first() : null;
        $activityId = $order->employment_id ?: ($booking->employment_id ?? null);
        $activity = ($order->employment_title_snapshot ?? null) ?: ($activityId ? $db->table('employments')->where('id', $activityId)->value('title') : null);
        $trainer = ($order->trainer_name_snapshot ?? null) ?: (isset($booking->trainer_id) ? $db->table('users')->where('id', $booking->trainer_id)->value('name') : null);

        $billiardTypes = ['pool', 'snooker', 'pyramid', 'pyramid_vip'];
        $tennisTypes = ['covered_soil', 'kypol_hard', 'hard', 'open_ground', 'padel', 'tenis', 'zpf'];
        $isTennis = !in_array($table->type ?? null, $billiardTypes, true)
            && (in_array($table->type ?? null, $tennisTypes, true) || ($settings['working_mode'] ?? null) === 'tennis'
                || $order->employment_id || ($order->employment_title_snapshot ?? null) || ($order->trainer_name_snapshot ?? null));
        $receiptType = $order->type_bar ? 'bar' : ($isTennis ? 'tennis' : 'billiards');

        $amount = (float) $order->amount;
        if ($order->booking_id) {
            $split = $db->getSchemaBuilder()->hasTable('tennis_order_split_payments')
                ? $db->table('tennis_order_split_payments')->where('order_id', $orderId)->first() : null;
            if ($split && $split->secondary_type === 'cashbox' && $split->secondary_cashbox_id) {
                $secondary = $db->table('cashboxes')->where('id', $split->secondary_cashbox_id)->value('title');
                $cashboxName = implode(' + ', array_filter([$cashboxName, $secondary]));
            }
            if (!$order->type_billiard) {
                // The split affects the payment label for bar orders, but not their total.
            } elseif ($split && $split->secondary_type !== 'pending') {
                $amount = round((float) $split->primary_amount + (float) $split->secondary_amount, 2);
            } else {
                $coupon = $db->table('couponlogs')->join('coupons', 'coupons.id', '=', 'couponlogs.coupon_id')
                    ->where('couponlogs.order_id', $orderId)->select('coupons.*', 'couponlogs.sum as paid_sum')->first();
                if ($coupon && (int) $coupon->type !== 3) {
                    if ($coupon->paid_sum !== null) {
                        $amount = (float) $coupon->paid_sum;
                    } elseif ((int) $coupon->type === 2) {
                        $amount = round(max(0, $amount - $amount * (int) $coupon->sum / 100), 2);
                    } elseif ((int) $coupon->type === 1) {
                        $discount = (int) $coupon->sum;
                        if ((int) $coupon->applying === 2) {
                            $discount -= $db->table('couponlogs')->where('coupon_id', $coupon->id)->where('order_id', '!=', $orderId)->sum('sum_sk');
                        }
                        if (in_array((int) $coupon->applying, [1, 2], true)) $amount = round(max(0, $amount - $discount), 2);
                    }
                }
            }
        }

        return compact('order', 'products', 'currency', 'amount', 'clubName', 'cashboxName', 'tableName', 'activity', 'trainer', 'receiptType');
    }

    private function tableName(object $table, string $language): string
    {
        $names = [
            'ua' => ['pool' => 'Пул', 'snooker' => 'Снукер', 'pyramid' => 'Піраміда', 'pyramid_vip' => 'Піраміда VIP', 'covered_soil' => 'Грунт', 'kypol_hard' => 'Купол', 'hard' => 'Хард', 'open_ground' => 'Трава', 'padel' => 'Падел', 'tenis' => 'Т.стіл', 'zpf' => 'ЗФП', 'football' => 'Футбольне поле', 'barnaya_stoyka' => 'Барна стійка'],
            'ru' => ['pool' => 'Пул', 'snooker' => 'Снукер', 'pyramid' => 'Пирамида', 'pyramid_vip' => 'Пирамида VIP', 'covered_soil' => 'Грунт', 'kypol_hard' => 'Купол хард', 'hard' => 'Хард', 'open_ground' => 'Трава', 'padel' => 'Падел', 'tenis' => 'Т.стіл', 'zpf' => 'ЗФП', 'football' => 'Футбольне поле', 'barnaya_stoyka' => 'Барна стійка'],
            'en' => ['pool' => 'Pull', 'snooker' => 'Snooker', 'pyramid' => 'Pyramid', 'pyramid_vip' => 'Pyramid VIP'],
        ];
        if ($language === 'tr') $language = 'ru';
        if ($language === 'en') $names['en'] += array_intersect_key($names['ru'], array_flip(['covered_soil', 'kypol_hard', 'hard', 'open_ground', 'padel', 'tenis', 'zpf', 'football', 'barnaya_stoyka']));
        $name = $names[$language][$table->type] ?? null;
        return '№'.$table->number.($name ? ' '.$name : '');
    }
}
