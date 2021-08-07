<?php
// src/Classes/AbstractPayment.php
namespace Pasargad\Api\Classes;

/**
 * Class AbstractPayment
 */
abstract class AbstractPayment {

    /** @var int $merchantId */
    protected $merchantId;

    /** @var int $terminalId */
    protected $terminalId;

    /** @var string $redirectUrl */
    protected $redirectUrl; 
    
    /** @var string $certificate */
    private $certificate;

    /** @var int $action */
    private $action;

    /** @var int $amount */
    private $amount;

    /** @var int $invoiceNumber */
    private $invoiceNumber;

    /** @var string $invoiceDate */
    private $invoiceDate;

    /** @var string $mobile */
    private $mobile = null;
    
    /** @var string $email */
    private $email = null;

    public function getMobile()
    {
        return $this->mobile;
    }

    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function __construct($action = 1003)
    {
        $this->action = $action;
    }

    public function getCertificate()
    {
        return $this->certificate;
    }

    public function setCertificate($code) {
        $this->certificate = $code;
    }

    public function getMerchantId()
    {
        return $this->merchantId;
    }

    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    public function getTerminalId()
    {
        return $this->terminalId;
    }

    public function setTerminalId($terminalId)
    {
        $this->terminalId = $terminalId;
    }

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl($url)
    {
        $this->redirectUrl = $url;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function setAmount($amount) 
    {
        $this->amount = $amount;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setInvoiceNumber($number)
    {
        $this->invoiceNumber = $number;
    }

    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceDate($date)
    {
        $this->invoiceDate = $date;
    }

    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }
}