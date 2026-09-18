<?php


namespace App\Services;
use App\Events\ViberUpdate;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramService
{

    /*
 *  Команда старт
 * отдача клавиатуры
 */
    public static function start($response)
    {
        $langConfig = config('viber_mess');

        $text = $response->message->text;
        $arr = explode(" ", $text);

        if (isset($arr[1])) {
            $code = $arr[1];

            $telegramcode = \DB::connection('billiard_login')->table('telegramcodes')->where('code', $code)
                ->where('status',0)
                ->orderBy('id', 'desc')
                ->first();

            if ($telegramcode) {
                \DB::connection('billiard_login')->table('telegramcodes')->where('code', $code)
                    ->where('status',0)
                    ->update([
                     'telegram_id'=>$response->message->from->id,
                     'name'   =>$response->message->from->first_name . ' ' . $response->message->from->last_name
                    ]);
                // отправка клавиатуры
                $lang = $response->message->from->language_code;
                $text_send = $langConfig['en']['sendNumber'];
                if (isset($langConfig[$lang])) {
                    $text_send = $langConfig[$lang]['sendNumber'];
                }
                $btn = Keyboard::button([
                    'text' => $text_send,
                    'request_contact' => true
                ]);
                $keyboard = Keyboard::make([
                    'keyboard' => [[$btn]],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);
                $response = Telegram::sendMessage([
                    'text' => $text_send,
                    'reply_markup' => $keyboard,
                    'chat_id' => $response->message->chat->id,
                ]);
            }
        }
    }

    //контакт отправил телефон c телеги
    public static function contact($response)
    {
        $telegramcode = \DB::connection('billiard_login')->table('telegramcodes')->where('telegram_id', $response->message->from->id)
            ->where('status',0)
            ->orderBy('id', 'desc')
            ->first();
        $telegram_id=$response->message->from->id;
        if ($telegramcode) {
            $phone = self::phoneValidate($response->message->contact->phone_number);
            if($telegramcode->phone==$phone){
                // генерируем событие
                event(new ViberUpdate([
                    'code' => $telegramcode->code,
                    'telegram_id' => $telegramcode->id,
                    'type'=>'telegram'
                ],'telegram'));

                // отправить ответ
                $langConfig = config('viber_mess');
                $lang = $response->message->from->language_code;
                $text_send = $langConfig['en']['back'];
                if (isset($langConfig[$lang])) {
                    $text_send = $langConfig[$lang]['back'];
                }
                $response = Telegram::sendMessage([
                    'text' => $text_send,
                    'chat_id' => $response->message->chat->id,
                ]);
            }
        }
    }

    public static function phoneValidate($phone)
    {
        // добавить  плюс везде
        $firstCharacter = substr($phone, 0, 1);
        if($firstCharacter!='+'){
            $phone='+'.$phone;
        }
        return $phone;
    }

    // логирование запроса
    public static function putLogIn($data)
    {
        file_put_contents(public_path() . "/log_my/telegram.txt", $data . "\n", FILE_APPEND);
    }

}
