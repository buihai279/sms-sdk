<?php

namespace DiagVN;

use Illuminate\Support\Facades\Log;

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

            return $response;
        } catch (\Exception $ex) {
            Log::error("Can not send message FPT: " . $number, ['message' => $message, 'error' => $ex->getMessage()]);
            report($ex);
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
}
