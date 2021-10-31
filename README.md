# Pasargad php-rest-sdk
PHP package to connect your application to Pasargad Internet Payment Gateway through RESTful API

# Installation
For installation, use `composer` package:

```bash
$ composer require pepco-api/php-rest-sdk
```

# Usage
 - To Read API Documentation, [Click Here! (دانلود مستندات کامل درگاه پرداخت)](https://www.pep.co.ir/wp-content/uploads/2019/06/1-__PEP_IPG_REST-13971020.Ver3_.00.pdf)
 - Save your private key into an `.xml` file inside your project directory.

## Redirect User to Payment Gateway
```php
// Use pasargad package 
use Pasargad\Pasargad;

// Always use try catch for handling errors
try {
    // Tip! Initialize this property in your payment service __constructor() method!
    $pasargad = new Pasargad(
      "YOUR_MERCHANT_CODE",
      "YOUR_TERMINAL_ID",
      "http://yoursite.com/redirect-url-here/",
      "certificate_file_location");
      //e.q: 
      // $pasargad = new Pasargad(123456,555555,"http://pep.co.ir/ipgtest","../cert/cert.xml");

    // Set Amount
    $pasargad->setAmount(100000); 

    // Set Invoice Number (it MUST BE UNIQUE) 
    $pasargad->setInvoiceNumber(4029);

    // set Invoice Date with below format (Y/m/d H:i:s)
    $pasargad->setInvoiceDate("2021/08/08 11:54:03");

    // Optional Parameters
    // ----------------------
    // User's Mobile and Email:
    $this->pasargad->setMobile("09121001234");
    $this->pasargad->setEmail("user@email.com");


    // IF YOU HAVE ACTIVATED "TAS-HIM" (تسهیم پرداخت), ADD SHABA AND PAYMENT SHARING PERCENTAGE/VALUE LIKE THIS:
    // شروع تسهیم ---------------------------------------------
    // فقط در صورتیکه قابلیت تسهیم شاپرکی را روی درگاه خود
    // فعال کرده‌اید از متد addPaymentType استفاده کنید.

    // تسهیم درصدی ۲۰ به ۸۰:
    $this->pasargad->addPaymentType("IR300570023980000000000000",PaymentItem::BY_PERCENTAGE, 20);
    $this->pasargad->addPaymentType("IR070570022080000000000001",PaymentItem::BY_PERCENTAGE, 80);

    // تسهیم مبلغی:
    $this->pasargad->addPaymentType("IR300570023980000000000000",PaymentItem::BY_VALUE, 20000);
    $this->pasargad->addPaymentType("IR070570022080000000000001",PaymentItem::BY_VALUE, 80000);
    // پایان تسهیم --------------------------------------------



    // get the Generated RedirectUrl from Pasargad API:
    $redirectUrl = $pasargad->redirect();
    var_dump($redirectUrl);
    // output example: https://pep.shaparak.ir/payment.aspx?n=bPo+Z8GLB4oh5W0KVNohihxCu1qBB3kziabGvO1xqg8Y=  

    // and redirect user to payment gateway:
    return header("Location: $redirectUrl");

    // ...or in Laravel/Symfony Controller (Controller extends Symfony\Component\HttpFoundation\Response):
    return  $this->redirect($redirectUrl);
} catch (\Exception $ex) {
      var_dump($ex->getMessage());
      die();
}
```

## Checking and Verifying Transaction
After Payment Process, User is going to be returned to your redirect_url.

payment gateway is going to answer the payment result with sending below parameters to your redirectURL (as `QueryString` parameters):
 - InvoiceNumber (IN field) 
 - InvoiceDate (ID field) 
 - TransactionReferenceID (tref field) 

Store this information in a proper data storage and let's check transaction result by sending a check api request to the Bank:

```php
// Set Transaction refrence id received in 
$pasargad->setTransactionReferenceId("636843820118990203"); 

// Set Unique Invoice Number that you want to check the result
$pasargad->setInvoiceNumber(4029);

// set Invoice Date of your Invoice
$pasargad->setInvoiceDate("2021/08/08 11:54:03");

// check Transaction result
var_dump($pasargad->checkTransaction());
```

Successful result is a PHP array:
```php
$result = [
  "TraceNumber" => 908768
  "ReferenceNumber" => 141113323710
  "TransactionDate" => "2021/09/16 12:08:28"
  "Action" => "1003"
  "TransactionReferenceID" => "637673907761796375"
  "InvoiceNumber" => "40209"
  "InvoiceDate" => "2021/09/16 11:54:03"
  "MerchantCode" => 4532980
  "TerminalCode" => 1718577
  "Amount" => 15000.0
  "TrxHashedCardNumber" => "9EB09984BF3F0FDA07D6055997A32F363276D4BD029AE0C870E60DCFC37ED02C"
  "TrxMaskedCardNumber" => "5022-29**-****-0682"
  "IsSuccess" => true
  "Message" => "عمليات به اتمام رسيد"
]
```
If you got `IsSuccess` with `true` value, so everything is O.K! otherwise, you will get an Exception.

Now, for your successful transaction, you should call `verifyPayment()` method to keep the money and Bank makes sure the checking process was done properly:


```php
// Set Transaction refrence id received in 
$pasargad->setAmount(15000); 

// Set Unique Invoice Number that you want to check the result
$pasargad->setInvoiceNumber(4029);

// set Invoice Date of your Invoice
$pasargad->setInvoiceDate("2021/08/08 11:54:03");

// verify payment:
return $pasargad->verifyPayment();
```

...and the successful response, is an array:
```php
$result = [
  "MaskedCardNumber" => "5022-29**-****-0682"
  "HashedCardNumber" => "2DDB1E270C598677AE328AA37C2970E3075E1DB6665C5AAFD131C59F7FAD99F23680536B07C140D24AAD8355EA9725A5493AC48E0F48E39D50B54DB906958182"
  "ShaparakRefNumber" => "141113323710"
  "IsSuccess" => true
  "Message" => "عمليات با موفقيت انجام شد"
]
```

## Payment Refund
If for any reason, you decided to cancel an order in early hours after taking the order (maximum 2 hours later), you can refund the client payment to his/her bank card.

for this, use `refundPayment()` method:

```php
// Set Unique Invoice Number that you want to check the result
$pasargad->setInvoiceNumber(4029);

// set Invoice Date of your Invoice
$pasargad->setInvoiceDate("2021/08/08 11:54:03");

// check Transaction result
return $pasargad->refundPayment();
```

# Support
Please use your credentials to login into [Support Panel](https://my.pep.co.ir)

Contact Author/Maintainer: [Reza Seyf](https://twitter.com/seyfcode)