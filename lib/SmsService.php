<?php

namespace DiagVN;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function sendMessage(
        string $number,
        string $message,
        bool   $isCheckWhiteList = false,
        string $provider = null
    ): ?bool
    {
        try {
            if (!$provider) {
                $provider = config('sms.provider');
            }
            $service = app('sms')->get($provider);
            $number = $service->formatPhoneNumber($number);
            $canSendMessage = $this->canSendMessage($number);
            if ($isCheckWhiteList && !$canSendMessage) {
                Log::error("Phone: " . $number . ' not in whitelist');
                return false;
            }
            $response = $service->send($number, $message);
            if (config('sms.log_sms')) {
                Log::debug("Phone: " . $number, ['message' => $message, 'FPT Response' => $response]);
            }

            $this->logSms($number, $response);

            return true;
        } catch (\Exception $ex) {
            Log::error("Can not send message FPT: " . $number, ['message' => $message, 'error' => $ex->getMessage()]);
            report($ex);
            $this->logSms($number, ['message' => $message, 'error' => $ex->getMessage()], false);
            return false;
        }
    }

    private function canSendMessage(string $number): bool
    {
        $whitelist = config('sms.whitelist');
        return !($whitelist && !str_contains($whitelist, $number));
    }

    private function logSms(
        string $number,
        array  $response,
        bool   $isSuccess = true
    )
    {
        if (config('sms.log_sms')) {
            dispatch(static function () use ($number, $isSuccess, $response) {
                DB::table('sms_logs')->insert([
                    'phone_number' => $number,
                    'is_success'   => $isSuccess,
                    'response'     => json_encode($response, JSON_THROW_ON_ERROR),
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now(),
                ]);
            })->afterCommit()->onQueue(config('sms.log_sms', 'default'));
        }
    }
}
