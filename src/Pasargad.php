<?php

namespace Pasargad;

use Pasargad\Classes\AbstractPayment;
use Pasargad\Classes\PaymentItem;
use Pasargad\Classes\PaymentType;
use Pasargad\Classes\RequestBuilder;
use Pasargad\Classes\RSA\RSAProcessor;

class Pasargad extends AbstractPayment
{
    /** @var RequestBuilder $api */
    private $api;

    /** @var string $token */
    private $token = null;

    /** @var boolean $safeMode */
    private $safeMode = true;

    /** @var array $paymentItems */
    private $paymentItems = [];

    /** @var boolean $multiPaymentMode */
    private $multiPaymentMode = false;

    /**
     * Address of gateway for getting token
     * @var string
     */
    const URL_GET_TOKEN = "https://pep.shaparak.ir/Api/v1/Payment/GetToken";

    /**
     * Redirect User with token to this URL
     * e.q: https://pep.shaparak.ir/payment.aspx?n=Token
     */
    const URL_PAYMENT_GATEWAY = "https://pep.shaparak.ir/payment.aspx";
    const URL_CHECK_TRANSACTION = 'https://pep.shaparak.ir/Api/v1/Payment/CheckTransactionResult';
    const URL_VERIFY_PAYMENT = 'https://pep.shaparak.ir/Api/v1/Payment/VerifyPayment';
    const URL_REFUND = 'https://pep.shaparak.ir/Api/v1/Payment/RefundPayment';

    /**
     * Pasargad Constructor
     * @var int $merchantCode
     * @var int $terminalCode
     * @var string $redirectAddress
     * @var string $certificateFile
     * @var string $action
     */
    public function __construct($merchantCode, $terminalCode, $redirectAddress, $certificateFile, $merchantName = null, $action = "1003", $safeMode = true)
    {
        $this->merchantId = $merchantCode;
        $this->terminalId = $terminalCode;
        $this->redirectUrl = $redirectAddress;
        $this->certificate = $certificateFile;
        $this->merchantName = $merchantName;
        $this->action = $action;
        $this->safeMode = $safeMode;
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
        $params["amount"] = $this->getAmount();
        $params["invoiceNumber"] = $this->getInvoiceNumber();
        $params["invoiceDate"] = $this->getInvoiceDate();
        $params['action'] = $this->getAction();
        $params['merchantCode'] = $this->getMerchantId();
        $params['terminalCode'] = $this->getTerminalId();
        $params['redirectAddress'] = $this->getRedirectUrl();
        $params['timeStamp'] = date("Y/m/d H:i:s");
        if ($this->getMobile()) {
            $params['mobile'] = $this->getMobile();
        }
        if ($this->getEmail()) {
            $params['email'] = $this->getEmail();
        }
        
        if ($this->multiPaymentMode) { 
            if ($this->getMerchantName() !== null) {
                $params['MerchantName'] = $this->getMerchantName();
            }
            $params['MultiPaymentData'] = base64_encode($this->generatePayment());
        }

        $sign = $this->sign(json_encode($params));
        $this->token = $this->api->send(static::URL_GET_TOKEN, RequestBuilder::POST, ["Sign" => $sign], $params, true, $this->safeMode);
        return $this->token;
    }

    /**
     * Redirect User to Gateway
     */
    public function redirect()
    {
        $token = null;
        if (!$this->token) {
            $this->token = $this->getToken();
        }
        $token = $this->token["Token"];
        return static::URL_PAYMENT_GATEWAY . "?n=" . $token;
    }

    /**
     * Verify Payment
     */
    public function verifyPayment()
    {
        $params['amount'] = $this->getAmount();
        $params['invoiceNumber'] = $this->getInvoiceNumber();
        $params['invoiceDate'] = $this->getInvoiceDate();
        $params['merchantCode'] = $this->getMerchantId();
        $params['terminalCode'] = $this->getTerminalId();
        $params['timeStamp'] = date("Y/m/d H:i:s");
        $sign = $this->sign(json_encode($params));
        $response = $this->api->send(static::URL_VERIFY_PAYMENT, RequestBuilder::POST, ["Sign" => $sign], $params, true, $this->safeMode);
        return $response;
    }

    /**
     * Check Transaction with referenceId
     */
    public function checkTransaction()
    {
        $params['invoiceNumber'] = $this->getInvoiceNumber();
        $params['invoiceDate'] = $this->getInvoiceDate();
        $params['merchantCode'] = $this->getMerchantId();
        $params['terminalCode'] = $this->getTerminalId();
        $params['transactionReferenceID'] = $this->getTransactionReferenceId();
        $response = $this->api->send(static::URL_CHECK_TRANSACTION, RequestBuilder::POST, [], $params, true, $this->safeMode);
        return $response;
    }

    /**
     * Refund Payment
     */
    public function refundPayment()
    {
        $params['invoiceNumber'] = $this->getInvoiceNumber();
        $params['invoiceDate'] = $this->getInvoiceDate();
        $params['merchantCode'] = $this->getMerchantId();
        $params['terminalCode'] = $this->getTerminalId();
        $params['timeStamp'] = date("Y/m/d H:i:s");
        $sign = $this->sign(json_encode($params));
        $response = $this->api->send(static::URL_REFUND, RequestBuilder::POST, ["Sign" => $sign], $params, true, $this->safeMode);
        return $response;
    }

    public function addPaymentType($iban,$type,$value) 
    {  
        $this->paymentItems[] = new PaymentItem($iban,$type,$value);
        $this->multiPaymentMode = true;
    }


    private function generatePayment()
    {
        $xw = xmlwriter_open_memory();
        xmlwriter_set_indent($xw, 1);
        $res = xmlwriter_set_indent_string($xw, ' ');
        xmlwriter_start_document($xw, '1.0', 'UTF-8');
        /** @var PaymentItem $item */
        foreach ($this->paymentItems as $item) {
            // <item>
            xmlwriter_start_element($xw, 'item');

                // <iban>
                xmlwriter_start_element($xw, 'iban');
                xmlwriter_text($xw, $item->getIban());
                xmlwriter_end_element($xw); 
                // </iban>


                // <type>
                xmlwriter_start_element($xw, 'type');
                xmlwriter_text($xw, $item->getType());
                xmlwriter_end_element($xw); 
                // </type>

                // <value>
                xmlwriter_start_element($xw, 'value');
                xmlwriter_text($xw, $item->getValue());
                xmlwriter_end_element($xw); 
                // </value>

            xmlwriter_end_element($xw); 
            // </item>
        }

        xmlwriter_end_document($xw);
        return xmlwriter_output_memory($xw);
    }
}
