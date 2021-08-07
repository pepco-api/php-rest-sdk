<?php

namespace Pasargad\Api\Classes;

use Curl\Curl;
use Pasargad\Api\Classes\RSA\RSAProcessor;

class RequestBuilder
{
    /**
     * The API get method.
     *
     * @const string
     */
    const GET = 'GET';

    /**
     * The API post method.
     *
     * @const string
     */
    const POST = 'POST';

    /**
     * The API put method.
     *
     * @const string
     */
    const PUT = 'PUT';

    /**
     * Address of gateway for getting token
     * @var string
     */
    const URL_GET_TOKEN = "https://pep.shaparak.ir/Api/v1/Payment/GetToken";

    /**
     * Address of payment gateway
     * @var string
     */
    const URL_GATEWAY = "https://pep.shaparak.ir/Api/v1/Payment/";

    /**
     * Redirect User with token to this URL
     * e.q: https://pep.shaparak.ir/payment.aspx?n=Token
     */
    const URL_PAYMENT_GATEWAY = "https://pep.shaparak.ir/payment.aspx";

    const URL_CHECK_TRANSACTION = 'https://pep.shaparak.ir/Api/v1/Payment/CheckTransactionResult';
    const URL_VERIFY_PAYMENT = 'https://pep.shaparak.ir/Api/v1/Payment/VerifyPayment';

    /**
     * The request.
     *
     * @var Curl $internalCurl
     */
    protected $internalCurl;

    /** @var array $headers */
    protected $headers;

    /** @var array $options */
    protected $options;

    public function __construct(array $params = [], array $headers = ['Accept' => 'application/json'])
    {
        $this->headers = $headers;
        $this->options = $params; 
        $this->internalCurl = new Curl();
    }

    /**
     * Build request.
     *
     * @param string $url    The url.
     * @param string $method The method.
     * @param array  $body   The body.
     *
     * @return bool|array Return response.
     *
     * @throws Exception Throw on unsupported $method use.
     * @throws \Exception Throw on API return invalid response.
     */
    public function send($url, $method = self::POST, array $headers = [], array $body = [])
    {
        $this->internalCurl->reset();
        foreach ($this->options as $option => $value) {
            $this->internalCurl->setOpt($option, $value);
        }

        foreach ($headers as $headerKey => $headerValue) {
            $this->internalCurl->setHeader($headerKey,$headerValue);
        }
        switch ($method) {
            case self::GET:
                $this->internalCurl->get($url);
                break;
            case self::POST:
                $this->internalCurl->setHeader('Content-Type', 'application/json;charset=UTF-8');
                $this->internalCurl->post($url, json_encode($body, JSON_UNESCAPED_UNICODE));
                break;
            case self::PUT:
                $this->internalCurl->setHeader('Content-Type', 'application/json;charset=UTF-8');
                $this->internalCurl->put($url, json_encode($body, JSON_UNESCAPED_UNICODE), true);
                break;
            default:
                throw new \Exception('Not supported method ' . $method . '.');
        }

        if (true === $this->internalCurl->error) {
            throw new \Exception(
                $this->internalCurl->error_message,
                $this->internalCurl->error_code
            );
        }

        if (false === empty($this->internalCurl->response)) {
            $json = json_decode($this->internalCurl->response, true);
            if (null === $json) {
                throw new \Exception(json_last_error_msg(), json_last_error());
            }

            if (true === isset($json['errorCode'])) {
                if (true === isset($json['description'])) {
                    throw new \Exception($json['description']);
                }
                throw new \Exception($json['errorCode']);
            }
            return $json;
        }

        return true;
    }
}
