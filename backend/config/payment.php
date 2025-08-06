<?php

return [

    // VNPay configuration
    'vnpay' => [
        'tmn_code' => env('VNPAY_TMN_CODE', '9AZ5L4B0'),
        'hash_secret' => env('VNPAY_HASH_SECRET', 'ENX7GP7VG112B51LB9V3BD00BOJNID93'),
        'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url' => env('VNPAY_RETURN_URL', 'http://localhost:3000/api/vnpay/result'),
    ],

    // MoMo configuration
    'momo' => [
        'access_key' => env('MOMO_ACCESS_KEY', 'F8BBA842ECF85'),
        'secret_key' => env('MOMO_SECRET_KEY', 'K951B6PE1waDMi640xX08PD3vg6EkVlz'),
        'partner_code' => env('MOMO_PARTNER_CODE', 'MOMO'),
        'redirect_url' => env('MOMO_REDIRECT_URL', 'http://localhost:3000/api/momo/result'),
        'ipn_url' => env('MOMO_IPN_URL', 'http://localhost:3000/api/momo/result'),
        'request_type' => env('MOMO_REQUEST_TYPE', 'payWithMethod'),
        'hostname' => env('MOMO_HOSTNAME', 'test-payment.momo.vn'),
        'endpoint' => env('MOMO_ENDPOINT', '/v2/gateway/api/create'),
    ],

    // VietQR configuration
    'vietqr' => [
        'api_url' => env('VIETQR_API_URL', 'https://api.vietqr.io/v2/generate'),
        'lookup_url' => env('VIETQR_LOOKUP_URL', 'https://api.vietqr.io/v2/lookup'),
        'banks' => [
            'VCB' => ['name' => 'Vietcombank', 'bin' => '970436'],
            'TCB' => ['name' => 'Techcombank', 'bin' => '970407'],
            'ACB' => ['name' => 'ACB', 'bin' => '970416'],
            'VIB' => ['name' => 'VIB', 'bin' => '970441'],
            'TPB' => ['name' => 'TPBank', 'bin' => '970423'],
            'STB' => ['name' => 'Sacombank', 'bin' => '970403'],
            'VPB' => ['name' => 'VPBank', 'bin' => '970432'],
            'MB'  => ['name' => 'MB Bank', 'bin' => '970422'],
        ],
        'demo_account' => [
            'bank_id' => 'VCB',
            'account_no' => '1013943138',
            'account_name' => 'QUY VAC XIN COVID-19',
        ],
        'return_url' => env('VIETQR_RETURN_URL', 'http://localhost:3000/api/vietqr/result'),
        'webhook_url' => env('VIETQR_WEBHOOK_URL', 'http://localhost:3000/api/vietqr/webhook'),
    ],

];
