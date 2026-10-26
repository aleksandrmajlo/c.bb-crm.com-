<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmsService
{
    public static function sendsms($phones, $code = false)
    {
        $requestId = (string) Str::uuid();
        $logContext = self::logContext('turbosms', $phones, $requestId);

        Log::info('SMS send requested', $logContext + [
            'event' => 'sms.requested',
            'custom_code_supplied' => $code !== false,
        ]);

        $result_send = [
            'suc' => 1,
            'text' => '',
            'id_sms' => null,
        ];
        if (env('APP_NAME') == 'Laravel_test') {
            $result_send = [
                'suc' => 1,
                'text' => '',
                'id_sms' => time(),
            ];

            Log::info('SMS delivery skipped', $logContext + [
                'event' => 'sms.skipped',
                'reason' => 'test_application',
                'message_id' => (string) $result_send['id_sms'],
            ]);

            return $result_send;
        }
        if (app()->environment('local')) {
            $result_send = [
                'suc' => 1,
                'text' => '',
                'id_sms' => 'local-'.Str::uuid(),
            ];

            Log::info('SMS delivery skipped', $logContext + [
                'event' => 'sms.skipped',
                'reason' => 'local_environment',
                'message_id' => (string) $result_send['id_sms'],
            ]);

            return $result_send;
        }
        try {
            // Подключаемся к серверу
            $client = new \SoapClient('http://turbosms.in.ua/api/wsdl.html');
            // Данные авторизации
            $login = env('TURBOSMS_LOGIN');
            $password = env('TURBOSMS_PASWORD');
            $auth = [
                'login' => $login,
                'password' => $password,
            ];
            $result = $client->Auth($auth);

            Log::info('SMS provider authorization completed', $logContext + [
                'event' => 'sms.authorization_completed',
                'provider_response' => self::providerResponse($result->AuthResult ?? null),
            ]);

            $result = $client->GetCreditBalance();
            $balance = (float) ($result->GetCreditBalanceResult ?? 0);

            Log::info('SMS balance checked', $logContext + [
                'event' => 'sms.balance_checked',
                'balance' => $balance,
                'has_balance' => $balance >= 1,
            ]);

            if ($balance < 1) {
                $result_send = [
                    'suc' => false,
                    'text' => 'Будь ласка, зв\'яжіться з Bb Crm',
                    'id_sms' => null,
                ];

                Log::warning('SMS was not sent because balance is insufficient', $logContext + [
                    'event' => 'sms.balance_insufficient',
                    'balance' => $balance,
                ]);

                return $result_send;
            }
            if (! $code) {
                $code = rand(1000, 9999);
            }
            $sms = [
                'sender' => 'BbCrm',
                'destination' => $phones,
                'text' => $code,
            ];

            Log::info('Sending SMS to provider', $logContext + [
                'event' => 'sms.send_started',
                'balance_before_send' => $balance,
            ]);

            $result = $client->SendSMS($sms);
            if (gettype($result->SendSMSResult->ResultArray) == 'string') {
                $result_send = [
                    'suc' => false,
                    'text' => $result->SendSMSResult->ResultArray,
                    'id_sms' => null,
                ];

                Log::error('SMS provider rejected the message', $logContext + [
                    'event' => 'sms.rejected',
                    'provider_response' => self::providerResponse($result->SendSMSResult->ResultArray, $phones),
                ]);

                return $result_send;
            }
            if ($result->SendSMSResult->ResultArray[0] != 'Сообщения успешно отправлены') {
                $result_send = [
                    'suc' => false,
                    'text' => $result->SendSMSResult->ResultArray[0].' '.$result->SendSMSResult->ResultArray[1],
                    'id_sms' => null,
                ];

                Log::error('SMS provider rejected the message', $logContext + [
                    'event' => 'sms.rejected',
                    'provider_response' => self::providerResponse($result->SendSMSResult->ResultArray, $phones),
                ]);

                return $result_send;
            }

            $id_sms = $result->SendSMSResult->ResultArray[1];

            $result_send = [
                'suc' => 1,
                'text' => '',
                'id_sms' => $id_sms,
            ];

            Log::info('SMS sent successfully', $logContext + [
                'event' => 'sms.sent',
                'message_id' => (string) $id_sms,
                'provider_status' => self::providerResponse($result->SendSMSResult->ResultArray[0]),
                'balance_before_send' => $balance,
            ]);

            return $result_send;
        } catch (Exception $e) {
            $text = $e->getMessage();
            $result_send = [
                'suc' => false,
                'text' => $text,
                'id_sms' => null,
            ];

            Log::error('SMS sending failed with an exception', $logContext + [
                'event' => 'sms.failed',
                'exception_class' => get_class($e),
                'error' => self::safeErrorMessage($text, $phones),
            ]);

            return $result_send;
        }

    }

    /*
     *  USA
     */
    public static function sendsmsUsa($phone, $code = false)
    {
        $requestId = (string) Str::uuid();
        $logContext = self::logContext('twilio', $phone, $requestId);

        Log::info('SMS send requested', $logContext + [
            'event' => 'sms.requested',
            'custom_code_supplied' => $code !== false,
        ]);

        $result_send = [
            'suc' => 1,
            'text' => '',
            'id_sms' => null,
        ];
        if (env('APP_NAME') == 'Laravel_test') {
            $result_send = [
                'suc' => 1,
                'text' => '',
                'id_sms' => time(),
            ];

            Log::info('SMS delivery skipped', $logContext + [
                'event' => 'sms.skipped',
                'reason' => 'test_application',
                'message_id' => (string) $result_send['id_sms'],
            ]);

            return $result_send;
        }
        if (app()->environment('local')) {
            $result_send = [
                'suc' => 1,
                'text' => '',
                'id_sms' => 'local-'.Str::uuid(),
            ];

            Log::info('SMS delivery skipped', $logContext + [
                'event' => 'sms.skipped',
                'reason' => 'local_environment',
                'message_id' => (string) $result_send['id_sms'],
            ]);

            return $result_send;
        }
        $conf = config('sms');
        if (! $code) {
            $code = rand(1000, 9999);
        }
        try {
            $client = new \Twilio\Rest\Client($conf['twillo_sid'], $conf['twillo_token']);

            // A balance lookup must not block delivery if Twilio temporarily
            // makes only the balance endpoint unavailable.
            try {
                $twilioBalance = $client->balance->fetch();
                Log::info('SMS balance checked', $logContext + [
                    'event' => 'sms.balance_checked',
                    'balance' => (float) $twilioBalance->balance,
                    'currency' => (string) $twilioBalance->currency,
                    'has_balance' => (float) $twilioBalance->balance > 0,
                ]);
            } catch (Exception $balanceException) {
                Log::warning('SMS balance check failed; delivery will still be attempted', $logContext + [
                    'event' => 'sms.balance_check_failed',
                    'exception_class' => get_class($balanceException),
                    'error' => self::safeErrorMessage($balanceException->getMessage(), $phone),
                ]);
            }

            Log::info('Sending SMS to provider', $logContext + [
                'event' => 'sms.send_started',
            ]);

            $message = $client->messages->create(
                $phone,
                [
                    'from' => '+18337591488',
                    'body' => $code,
                ]
            );

            $id_sms = Str::uuid();
            $result_send = [
                'suc' => 1,
                'text' => '',
                'id_sms' => $id_sms,
            ];

            Log::info('SMS sent successfully', $logContext + [
                'event' => 'sms.sent',
                'message_id' => (string) $id_sms,
                'provider_message_id' => (string) ($message->sid ?? ''),
                'provider_status' => (string) ($message->status ?? 'accepted'),
            ]);

            return $result_send;
        } catch (Exception $e) {
            $text = $e->getMessage();
            $result_send = [
                'suc' => false,
                'text' => $text,
                'id_sms' => null,
            ];

            Log::error('SMS sending failed with an exception', $logContext + [
                'event' => 'sms.failed',
                'exception_class' => get_class($e),
                'error' => self::safeErrorMessage($text, $phone),
            ]);

            return $result_send;
        }

    }

    public static function validatePhoneUsa($phone)
    {
        $firstCharacter = substr($phone, 0, 2);
        if ($firstCharacter == '+1') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Common structured context used to correlate all stages of one attempt.
     * Recipients are masked so production logs do not contain full phone numbers.
     */
    private static function logContext(string $provider, $phones, string $requestId): array
    {
        $recipients = is_array($phones) ? $phones : [$phones];

        return [
            'request_id' => $requestId,
            'provider' => $provider,
            'environment' => app()->environment(),
            'recipients' => array_map(
                static fn ($phone) => self::maskPhone((string) $phone),
                array_values($recipients)
            ),
            'recipients_count' => count($recipients),
        ];
    }

    private static function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return 'unknown';
        }

        $visibleStart = substr($digits, 0, min(3, strlen($digits)));
        $visibleEnd = strlen($digits) > 3 ? substr($digits, -3) : '';
        $maskedLength = max(3, strlen($digits) - strlen($visibleStart) - strlen($visibleEnd));

        return $visibleStart.str_repeat('*', $maskedLength).$visibleEnd;
    }

    /**
     * Provider responses can be arrays or SOAP objects. Store a bounded string
     * so a malformed upstream response cannot flood the application log.
     */
    private static function providerResponse($response, $phones = []): ?string
    {
        if ($response === null) {
            return null;
        }

        if (is_scalar($response)) {
            return self::safeErrorMessage((string) $response, $phones, 500);
        }

        $encoded = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return self::safeErrorMessage($encoded !== false ? $encoded : gettype($response), $phones, 500);
    }

    private static function safeErrorMessage(string $message, $phones, int $limit = 1000): string
    {
        $recipients = is_array($phones) ? $phones : [$phones];

        foreach ($recipients as $phone) {
            $rawPhone = (string) $phone;
            $digits = preg_replace('/\D+/', '', $rawPhone) ?? '';
            $masked = self::maskPhone($rawPhone);
            $sensitiveVariants = array_filter([$rawPhone, $digits, $digits !== '' ? '+'.$digits : '']);

            $message = str_replace($sensitiveVariants, $masked, $message);
        }

        return Str::limit($message, $limit);
    }
}
