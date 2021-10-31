<?php

namespace Pasargad\Classes;

/**
 * Class AbstractBossIPG
 */
abstract class AbstractBossIPG
{

    /** @var string $username */
    protected $username;

    /** @var string $password */
    protected $password;
    
    /** @var string $token */
    protected $token = null;

    /** @var string $redirectAddress */
    protected $redirectAddress;

    /** @var string $terminalCode */
    protected $terminalCode;
 
    /** @var string $amount */
    protected $amount;
    
    /** @var string $action */
    protected $action;

    /** @var string $description */
    protected $description;
    
    /** @var string $invoice */
    protected $invoice;

    /** @var string $invoiceDate */
    protected $invoiceDate;
    
    /** @var string $mobileNumber */
    protected $mobileNumber;

    /** @var string $payerMail */
    protected $payerMail;

    /** @var string $payerName */
    protected $payerName;
    
    /** @var string $platform */
    protected $platform;

    /** @var string $urlId */
    protected $urlId;


    /** @var string $billId */
    protected $billId;

    /** @var string $paymentId */
    protected $paymentId;

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    } 

    
    public function setToken($token)
    {
        $this->token = $token;
    }


    public function getRedirectAddress()
    {
        return $this->redirectAddress;
    }

    public function setRedirectAddress($redirectAddress)
    {
        $this->redirectAddress = $redirectAddress;
    }

    public function getTerminalCode()
    {
        return $this->terminalCode;
    }

    public function setTerminalCode($terminalCode)
    {
        $this->terminalCode = strval($terminalCode);
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }
 
    
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = strval($description);
    } 
 
    public function getInvoice()
    {
        return $this->invoice;
    }

    public function setInvoice($invoice)
    {
        $this->invoice = strval($invoice);
    }
 
    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(\Datetime $invoiceDate)
    {
        $this->invoiceDate = $invoiceDate->format('Y-m-d\TH:i:s.u');
    }
 
    public function getMobileNumber()
    {
        return $this->mobileNumber;
    }

    public function setMobileNumber($mobileNumber)
    {
        $this->mobileNumber = $mobileNumber;
    }
 
    public function getPayerMail()
    {
        return $this->payerMail;
    }

    public function setPayerMail($payerMail)
    {
        $this->payerMail = $payerMail;
    }

    public function getPayerName()
    {
        return $this->payerName;
    }

    public function setPayerName($payerName)
    {
        $this->payerName = $payerName;
    }

    public function getPlatform()
    {
        $validPlatforms = [
            "WEB"
        ];
        return $this->platform;
    }

    public function setPlatform($platform)
    {
        $this->platform = $platform;
    } 

    public function getUrlId()
    {
        return $this->urlId;
    }

    public function setUrlId($urlId)
    {
        $this->urlId = $urlId;
    }

    public function getBillId()
    {
        return $this->billId;
    }

    public function setBillId($billId)
    {
        $this->billId = $billId;
    }

    public function getPaymentId()
    {
        return $this->paymentId;
    }

    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
    }

 
}
