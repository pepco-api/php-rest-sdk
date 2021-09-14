<?php
namespace Pasargad\Classes;

use Curl\Curl;

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
     * The request.
     *
     * @var Curl $internalCurl
     */
    protected $internalCurl;

    /** @var array $headers */
    protected $headers;

    /** @var array $options */
    protected $options = [];

    /**
     * RequestBuilder Class constructor.
     */
    public function __construct()
    {
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
    public function send($url, $method = self::POST, array $headers = [], array $body = [], $encodeJson = false)
    {
        $this->internalCurl->reset();
        foreach ($this->options as $option => $value) {
            $this->internalCurl->setOpt($option, $value);
        }

        $this->internalCurl->setHeader('Content-Type', 'application/json');
        $this->internalCurl->setHeader('Accept', 'application/json');
        foreach ($headers as $headerKey => $headerValue) {
            $this->internalCurl->setHeader($headerKey, $headerValue);
        }
        switch ($method) {
            case self::GET:
                $this->internalCurl->get($url);
                break;
            case self::POST:
                $this->internalCurl->post($url, $encodeJson ? json_encode($body) : $body);
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
            $json = json_decode($this->internalCurl->response,true);
            if ($json === null) {
                throw new \Exception(json_last_error_msg(), json_last_error());
            }
            if ($json['IsSuccess'] == false) {
                throw new \Exception($json['Message']);
            }
            return $json;
        }

        return true;
    }
}
