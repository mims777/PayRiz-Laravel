# PayRiz-Laravel

Payment Gateway : http://payriz.ir/
<br>
PayRiz-laravel package Deveolper : M.Saleh Feyzi

## PayRiz.ir

Safe and secure payment gateway

## How To Use

Add Controllers Fils To Controllers Dir
<br>
The URIs that should be excluded from CSRF verification.
<br>
Middleware/VerifyCsrfToken.php

    protected $except = [
        'payment/verify', // Add
    ];
    
## Router 

Add values to your router

    Route::any('/payment/{orderid}/{amount}/{payInfo}' , 'PaymentController@NewPay')->name('payment');
    
    Route::any('/payment/verify' , 'PaymentController@Verify')->name('paymentVerify');

## Support

Mail : saleh.faezy77@gmail.com
<br/>
Telegram : @re_use
