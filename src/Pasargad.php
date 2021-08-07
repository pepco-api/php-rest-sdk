<?php
namespace Pasargad\Api;
/**
 * PEP bill payment SDK.
 * // TODO: PARAMS PROBLEM!
 * @package   Pasargad\Api
 * @author    Reza Seyf <rseyf@hotmail.com>
 * @copyright 2021 (c) Pardakht Electronic Pasargad
 * @license   Apache 2.0
 */
use Pasargad\Api\Classes\AbstractPayment;
use Pasargad\Api\Classes\RequestBuilder;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\Common\PrivateKey;
class Pasargad extends AbstractPayment
{
    /** @var array $params */
    private $params = [];

    /** @var string $signedParams */
    private $signedParams;

    /** @var string $token */
    private $token;

    /** @var RequestBuilder $requestBuilder */
    private $requestBuilder;

    public function __construct($action = 1003)
    {
        parent::__construct($action);
        $this->requestBuilder = new RequestBuilder();
    }
    
    /**
     * Add parameter to request parameters ($this->params variable)
     * @var string $key
     * @var string $value
     */
    public function addParam($key,$value)
    {
        $this->params[$key] = $value;
        return $this->getParams();
    }

    public function getParams()
    {
        $this->params = [
            "action" => $this->getAction(),
            "merchantCode" => $this->getMerchantId(),
            "terminalCode" => $this->getTerminalId(),
            "redirectAddress" => $this->getRedirectUrl(),
            "timeStamp" => $this->getTimestamp(),
            "amount" => '',
            "invoiceNumber" => '',
            "invoiceDate" => '',
        ];
        if ($this->getEmail()) {
            $this->params["email"] = $this->getEmail();
        }
        if ($this->getMobile()) {
            $this->params["mobile"] = $this->getMobile();
        }
        return $this->params;
    }

    private function getSignedParams()
    {
        /** @var PrivateKey $key */
        $key = RSA::loadFormat('PKCS1', file_get_contents($this->getCertificate()), 'password');
        $this->signedParams = base64_encode($key->sign(json_encode($this->getParams())));
        return $this->signedParams;
    }

    /**
     * Get Timestamp 
     */
    private function getTimestamp()
    {
        return date("Y/m/d H:i:s");
    }

    /**
     * Get Token for the next step
     * Redirect User to https://pep.shaparak.ir/payment.aspx?n=Token
     */
    public function getToken()
    {
        $this->token = $this->requestBuilder->send(
            RequestBuilder::URL_GET_TOKEN,
            RequestBuilder::POST,
            ["Sign", $this->getSignedParams()],
            $this->getParams()
        );
        return $this->token;
    }

    /**
     * Get Redirect URL to Payment Gateway
     */
    public function getRedirectToGatewayURL()
    {
        return RequestBuilder::URL_PAYMENT_GATEWAY . "?n=" . $this->token;
    }

    /**
     * Redirect To Url Using header() method
     * -------
     * Notice!
     * -------
     * If you are using this package in a framework Like Symfony or Laravel, 
     * Try to use HttpFoundation Response object
     * 
     */
    public function redirectToUrl()
    {
        return header("Location: ". $this->getRedirectToGatewayURL());
    }

    /**
     * Check Transaction Result
     */
    public function checkTransactionResult()
    {
        $respose = $this->requestBuilder->send(
            RequestBuilder::URL_CHECK_TRANSACTION,
            RequestBuilder::POST,
            [],
            $this->getParams());
    }

    /**
     * Verify Payment 
     */
    public function verifyPayment()
    {
        return $this->requestBuilder->send(
            RequestBuilder::URL_VERIFY_PAYMENT,
            RequestBuilder::POST,
            ["Sign", $this->getSignedParams()],
            $this->getParams()
        );
    }
}
