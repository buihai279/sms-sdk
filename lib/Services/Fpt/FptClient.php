<?php

namespace DiagVN\Services\Fpt;

use DiagVN\Fpt\TechAPI\Api\SendBrandnameOtp;
use DiagVN\Fpt\TechAPI\Auth\AccessToken;
use DiagVN\Fpt\TechAPI\Auth\ClientCredentials;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use DiagVN\Fpt\TechAPI\Client;
use DiagVN\Fpt\TechAPI\Constant;
use DiagVN\Fpt\TechAPI\Exception;
use DiagVN\SmsClient;

class FptClient implements SmsClient
{
    /** @var string[] */
    private $config;

    /** @var string[] */
    private $credentials;

    /**
     * FtpClient constructor.
     * @param string[] $credentials
     * @param string[] $options
     */
    public function __construct($credentials, $options)
    {
        $this->config = $options;
        $this->credentials = $credentials;

        // config api
        Constant::configs(array(
            'mode'            => $options['sms.fpt.mode'],
            'connect_timeout' => 15,
        ));
    }


    /**
     * Get Fpt authorization
     * @return ClientCredentials
     */
    private function getTechAuthorization()
    {
        $client = new Client(
            $this->credentials['client_id'],
            $this->credentials['client_secret'],
            $this->config['sms.fpt.scopes']
        );

        return new ClientCredentials($client);
    }


    /**
     * @param string $phone
     * @param string $message
     * @return mixed
     * @throws \Exception
     */
    public function send(string $phone, string $message)
    {
        if (empty($phone))
            throw new \Exception("Please provide phone number");

        if (empty($message))
            throw new \Exception("Please provide message");

        $arrMessage = [
            'Phone'      => $phone,
            'BrandName'  => $this->config['fpt.brand_name'],
            'Message'    => $message
        ];

        $apiSendBrandName = new SendBrandnameOtp($arrMessage);

        try {
            $oGrantType      = $this->getTechAuthorization();
            $arrResponse     = $oGrantType->execute($apiSendBrandName);

            if (!empty($arrResponse['error'])) {
                AccessToken::getInstance()->clear();

                throw new Exception($arrResponse['error_description'], $arrResponse['error']);
            }

            return $arrResponse;
        } catch (\Exception $ex) {
            throw new $ex;
        }
    }

    public function formatPhoneNumber(string $number): string
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $swissNumberProto = $phoneUtil->parse($number, config('sms.country_code'));
        $nationalPhone = $phoneUtil->format($swissNumberProto, PhoneNumberFormat::INTERNATIONAL);
        $countryCode = $phoneUtil->getCountryCodeForRegion(config('sms.country_code'));
        if (strpos($nationalPhone, '0') === 0) {
            $nationalPhone = $countryCode . mb_substr($nationalPhone, 1, strlen($nationalPhone) - 1);
        }
        $number = str_replace('+', '', $nationalPhone);
        $number = str_replace(' ', '', $number);

        return $number;
    }
}
