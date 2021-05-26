<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;



class RecaptchaService
{
    private $parameterBag;
    private $logger;

    public function __construct(ParameterBagInterface $parameterBag, LoggerInterface $logger)
    {
        $this->parameterBag = $parameterBag;
        $this->logger = $logger;
    }


    /**
     * @param $recaptchaResponse
     * @param $remoteAddr
     * @return bool
     */
    public function recaptchaGetProbabilityThatUserIsHuman($recaptchaResponse, $remoteAddr)
    {
        $verPostData = [
            'secret' => $this->parameterBag->get('recaptcha_secret'),
            'response' => $recaptchaResponse,
            'remoteip' => $remoteAddr
        ];
        $recaptchaProxy = $this->parameterBag->get('recaptcha_proxy');
        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($verPostData));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if ($recaptchaProxy) {
            curl_setopt($ch, CURLOPT_PROXY, $recaptchaProxy);
        }
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->logger->info('Checking captcha for ' . $remoteAddr);

        if (200 == $httpcode) {
            $responseArr = json_decode($response, true);

            if (isset($responseArr['error-codes']) && $responseArr['error-codes']) {
                foreach ($responseArr['error-codes'] as $errorcode) {
                    $this->logger->error('Recaptcha remote error ' . $errorcode);
                }
                return false;
            } else {
                return $responseArr['score'];
            }
        } else {
            $this->logger->error('Recaptcha response error: ' . $response);
            return false;
        }
    }


}