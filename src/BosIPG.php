<?php

namespace Pasargad;

use Pasargad\Classes\AbstractPayment;
use Pasargad\Classes\RequestBuilder;
use Pasargad\Classes\AbstractBosIPG;
use Pasargad\Classes\RSA\RSAProcessor;


class BosIPG extends AbstractBosIPG
{

    const PLATFORM_WEB = "WEB";
    
    const PURCHASE = 8;
    const BILL = "Bill";
    const MCI_DIRECT = "MCI";
    const IRANCELL_DIRECT = "MTN";
    const RIGHTEL_DIRECT = "RTL";
    const MCI_PIN = "MCI";
    const MTN_PIN = "MTN";
    const RIGHTEL_PIN = "RTL";

    private static $serviceCodes = [
        self::MCI_DIRECT => 1,
        self::IRANCELL_DIRECT => 2,
        self::RIGHTEL_DIRECT => 3,
        self::BILL => 4,
        self::MCI_PIN => 5,
        self::MTN_PIN => 6,
        self::RIGHTEL_PIN => 7,
        self::PURCHASE => 8,
    ]; 

    /** @var RequestBuilder $api */
    private $api;


    /**
     * Address of gateway for getting token
     * @var string
     */
    const URL_GET_TOKEN = "https://pep.shaparak.ir/bos/token/getToken";
    const URL_PURCHASE = "https://pep.shaparak.ir/bos/api/payment/purchase";
    const URL_VERIFY_TRANSACTION = "https://pep.shaparak.ir/bos/api/payment/verify-transactions";
    const URL_PURCHASE_MOBILE_CHARGE = "https://pep.shaparak.ir/bos/api/payment/pre-transaction";

    /**
     * BosIPG Constructor
     * @var string $username
     * @var string $password
     * @var string $redirectAddress
     * @var string $terminalCode
     * @var string $platform
     */
    public function __construct($username, $password, $redirectAddress, $terminalCode, $platform = self::PLATFORM_WEB)
    {
        $this->username = $username;
        $this->password = $password;
        $this->redirectAddress = $redirectAddress;
        $this->terminalCode = $terminalCode;
        $this->platform = $platform;
        $this->api = new RequestBuilder();
    }

    /**
     * Sign data using RSA key
     * @var array $data
     */
    private function sign($data)
    {
        $processor = new RSAProcessor($this->certificate);
        return base64_encode($processor->sign(sha1($data, true)));
    }


    /**
     * Get Token to prepare user for redirecting to payment gateway
     */
    public function getToken()
    {
        $params["username"] = $this->getUsername();
        $params["password"] = $this->getPassword();
        $response = $this->api->send(static::URL_GET_TOKEN, RequestBuilder::POST, [], $params, true);
        $token = $response['token'];
        $this->setToken($token);
        return $token;
    }

    /**
     * Redirect User to Gateway
     */
    public function redirect()
    {
        // Mandatory Parameters
        $params["amount"] = $this->getAmount();
        $params["invoice"] = $this->getInvoice();
        $params["invoiceDate"] = $this->getInvoiceDate();
        $params["serviceCode"] = self::$serviceCodes[self::PURCHASE];

        // Optional Parameters
        $params["description"] = $this->getDescription();
        $params["payerMail"] = $this->getPayerMail();
        $params["payerName"] = $this->getPayerName();

        // Strong Authorization
        $params["mobileNumber"] = $this->getMobileNumber();

        // Filled by system
        $params["platform"] = $this->getPlatform();
        $params["callbackApi"] = $this->getRedirectAddress();
        $params["terminalNumber"] = $this->getTerminalCode();

        $response = $this->api->send(
            static::URL_PURCHASE,
            RequestBuilder::POST,
            ["Authorization" => "Bearer " . $this->getToken()],
            $params,
            true
        );
        $resultMsg = $response["resultMsg"];
        $resultCode = $response["resultCode"];
        if ($resultCode !== 0) {
            throw new \Exception("Error[$resultCode]: $resultMsg");
        }
        return $response["data"];
    }

    /**
     * Verify Transaction
     */
    public function verifyTransaction()
    {
        $params['invoice'] = $this->getAmount();
        $params['urlId'] = $this->getUrlId();
        $response = $this->api->send(
            static::URL_VERIFY_TRANSACTION,
            RequestBuilder::POST, 
            ["Authorization" => "Bearer " . $this->getToken()],
            $params,
            true); 
        $resultMsg = $response["resultMsg"];
        $resultCode = $response["resultCode"];
        if ($resultCode !== 0) {
            throw new \Exception("Error[$resultCode]: $resultMsg");
        }
        return $response["data"];
    }
 
    /**
     * Purchase Cellphone Charge
     */
    public function purchaseCellphoneCharge()
    {
        // Mandatory Parameters
        $params["amount"] = $this->getAmount();
        $params["invoice"] = $this->getInvoice();
        $params["invoiceDate"] = $this->getInvoiceDate();
        $params["serviceCode"] = $this->getPassword();

        // Optional Parameters
        $params["description"] = $this->getDescription();
        $params["payerMail"] = $this->getPayerMail();
        $params["payerName"] = $this->getPayerName();

        // Strong Authorization
        $params["mobileNumber"] = $this->getMobileNumber();

        // Filled by system
        $params["platform"] = $this->getPlatform();
        $params["callbackApi"] = $this->getRedirectAddress();
        $params["terminalNumber"] = $this->getTerminalCode();

        $response = $this->api->send(
            static::URL_PURCHASE,
            RequestBuilder::POST,
            ["Authorization" => "Bearer " . $this->getToken()],
            $params,
            true
        );
        $resultMsg = $response["resultMsg"];
        $resultCode = $response["resultCode"];
        if ($resultCode !== 0) {
            throw new \Exception("Error[$resultCode]: $resultMsg");
        }
        return $response["data"];
    }
}
