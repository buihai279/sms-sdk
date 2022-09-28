<?php

namespace DiagVN;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SmsService
{
    public function sendMessage(
        string $number,
        string $message,
        bool $isCheckWhiteList = true
    ): ?array {
        try {
            $service = app('sms')->get(config('sms.provider'));
            $number = $service->formatPhoneNumber($number);
            $canSendMessage = $this->canSendMessage($number);
            if ($isCheckWhiteList && !$canSendMessage) {
                Log::error("Phone: " . $number . ' not in whitelist');
                return null;
            }
            $response = $service->send($number, $message);
            if (config('sms.log_sms')) {
                Log::debug("Phone: " . $number, ['message' => $message, 'FPT Response' => $response]);
            }

            $this->logSms($number, $response);

            return $response;
        } catch (\Exception $ex) {
            Log::error("Can not send message FPT: " . $number, ['message' => $message, 'error' => $ex->getMessage()]);
            report($ex);
            $this->logSms($number, $response, false);
            return [];
        }
    }

    private function canSendMessage(string $number): bool
    {
        $whitelist = config('sms.whitelist');
        if ($whitelist && strpos($whitelist, $number) === false) {
            return false;
        }

        return true;
    }

    private function logSms(
        string $number,
        array $response,
        bool $isSuccess = true,
    ) {
        DB::table('sms_logs')->insert([
            'phone_number' => $number,
            'is_success' => $isSuccess,
            'response' => json_encode($response),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
