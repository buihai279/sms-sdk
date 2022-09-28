<?php

namespace DiagVN;

use DiagVN\Fpt\TechAPI\Exception;

interface SmsClient
{
    /**
     * @param string $phone
     * @param string $message
     * @return mixed
     * @throws \Exception
     */
    public function send($phone, $message);
}
