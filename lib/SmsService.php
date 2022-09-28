<?php

namespace DiagVN;

use Illuminate\Support\Facades\Log;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class SmsService
{
    public function sendMessage(
        string $number,
        string $message,
        bool $isCheckWhiteList = true
    ): ?array {
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $swissNumberProto = $phoneUtil->parse($number, config('sms.country_code'));
            $nationalPhone = $phoneUtil->format($swissNumberProto, PhoneNumberFormat::INTERNATIONAL);
            $countryCode = $phoneUtil->getCountryCodeForRegion(config('sms.country_code'));
            if (strpos($nationalPhone, '0') === 0) {
                $nationalPhone = $countryCode . mb_substr($nationalPhone, 1, strlen($nationalPhone) - 1);
            }
            $number = str_replace('+', '', $nationalPhone);
            $number = str_replace(' ', '', $number);
            $canSendMessage = $this->canSendMessage($number);

            if ($isCheckWhiteList && !$canSendMessage) {
                Log::error("Phone: " . $number . ' not in whitelist');
                return null;
            }
            $service = app('sms')->get(config('sms.provider'));
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
