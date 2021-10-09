<?php
namespace Pasargad;

use Pasargad\Classes\RequestBuilder;
use Pasargad\Classes\AbstractBossIPG;

class BossIPG extends AbstractBossIPG
{
    private const PLATFORM_WEB = "WEB";
    private const PURCHASE = "Purchase";
    private const BILL = "Bill";

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

    private static $productCodes = [
        self::MCI_DIRECT => 95,
        self::IRANCELL_DIRECT => 93,
        self::RIGHTEL_DIRECT => 94,
        self::MCI_PIN => 200095,
        self::MTN_PIN => 200093,
        self::RIGHTEL_PIN => 200094,
    ];

    private static $validCellphoneCharges = [
        self::MCI_DIRECT,
        self::IRANCELL_DIRECT,
        self::RIGHTEL_DIRECT,
        self::MCI_PIN,
        self::MTN_PIN,
        self::RIGHTEL_PIN,
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
    const URL_PAYMENT_INQUIRY = "https://pep.shaparak.ir/bos/api/payment/payment-inquiry";

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
     * Get Token to prepare user for redirecting to payment gateway
     */
    public function getToken()
    {
        if ($this->token != null) {
            return $this->token;
        }
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
        
        // Optional Parameters
        $params["description"] = $this->getDescription();
        $params["payerMail"] = $this->getPayerMail();
        $params["payerName"] = $this->getPayerName();
        
        // Strong Authorization
        $params["mobileNumber"] = $this->getMobileNumber();
        
        // Filled by system
        $params["serviceCode"] = self::$serviceCodes[self::PURCHASE];
        $params["platform"] = self::PLATFORM_WEB;
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
        $params['invoice'] = $this->getInvoice();
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
    public function purchaseCellphoneCharge($type = self::IRANCELL_DIRECT)
    {
        if (!in_array($type,self::$validCellphoneCharges)) {
            $validTypes = null;
            foreach (self::$validCellphoneCharges as $validType) {
                $validTypes .= "'$validType'";
            }
            throw new \Exception("current type '$type' is not supported. supported types: $validTypes");
        }

        // Mandatory Parameters
        $params["amount"] = $this->getAmount();
        $params["invoice"] = $this->getInvoice();
        $params["invoiceDate"] = $this->getInvoiceDate();

        $params["productCode"] = self::$productCodes[$type];
        $params["serviceCode"] = self::$serviceCodes[$type];
        $params["serviceType"] = $type;

        // Optional Parameters
        $params["description"] = $this->getDescription();
        $params["payerMail"] = $this->getPayerMail();
        $params["payerName"] = $this->getPayerName();

        // Strong Authorization
        $params["mobileNumber"] = $this->getMobileNumber();

        // Filled by system
        $params["platform"] = self::PLATFORM_WEB;
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
    public function paymentInquiry()
    {
        $params['invoice'] = $this->getInvoice();
        $response = $this->api->send(
            static::URL_PAYMENT_INQUIRY,
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
     * Pay Bill
     * @deprecated
     */
    public function payBill()
    { 
        return false;
        // // Mandatory Parameters
        // $params["amount"] = $this->getAmount();
        // $params["invoice"] = $this->getInvoice();
        // $params["invoiceDate"] = $this->getInvoiceDate();
        // $params["billId"] = $this->getBillId();
        // $params["paymentId"] = $this->getPaymentId();

        // $params["serviceCode"] = self::$serviceCodes[self::BILL];
        // $params["serviceType"] = self::BILL;

        // // Optional Parameters
        // $params["description"] = $this->getDescription();
        // $params["payerMail"] = $this->getPayerMail();
        // $params["payerName"] = $this->getPayerName();

        // // Strong Authorization
        // $params["mobileNumber"] = $this->getMobileNumber();

        // // Filled by system
        // $params["platform"] = self::PLATFORM_WEB;
        // $params["callbackApi"] = $this->getRedirectAddress();
        // $params["terminalNumber"] = $this->getTerminalCode();

        // $response = $this->api->send(
        //     static::URL_PURCHASE,
        //     RequestBuilder::POST,
        //     ["Authorization" => "Bearer " . $this->getToken()],
        //     $params,
        //     true
        // );

        // $resultMsg = $response["resultMsg"];
        // $resultCode = $response["resultCode"];
        // if ($resultCode !== 0) {
        //     throw new \Exception("Error[$resultCode]: $resultMsg");
        // }
        // return $response["data"];
    }

    

}
