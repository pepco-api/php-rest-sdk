<?php

namespace Pasargad\Classes;

use JsonSerializable;

class PaymentItem implements JsonSerializable
{
    const BY_VALUE = 0;
    const BY_PERCENTAGE = 1;

    private static $validTypes = [
        self::BY_VALUE,
        self::BY_PERCENTAGE
    ];

    private $iban;
    private $type;
    private $value;

    public function __construct($iban, $type, $value)
    {
        $this->setIban($iban);
        $this->setType($type);
        $this->setValue($value);
    }

    public function setIban($iban)
    {
        // TODO: CHECK IBAN 
        $this->iban = $iban;
    }

    public function setType($type)
    {
        if (!in_array($type,self::$validTypes)) {
            throw new \Exception("MultiPayment Type is not valid: $type");
        }
        $this->type = $type;
    }

    public function setValue($value)
    {
        if ($this->type == self::BY_PERCENTAGE &&  intval($value) > 100) {
            throw new \Exception("For Percentage payment sharing type, you should set a value between 0 & 100");
        }
        $this->value = $value;
    }

    public function getIban()
    {
        return $this->iban;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return [
            "iban" => $this->getIban(),
            "type" => $this->getType(),
            "value" => $this->getValue(),
        ];
    }
}
