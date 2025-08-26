<?php

return [
    'vnpay' => [
        'tmn_code'    => env('VNPAY_TMN_CODE', '9AZ5L4B0'),
        'hash_secret' => env('VNPAY_HASH_SECRET', 'ENX7GP7VG112B51LB9V3BD00BOJNID93'),
        'url'         => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url'  => env('VNPAY_RETURN_URL', 'http://localhost:8000/api/group-orders/payments/vnpay/return'), // ← BE
    ],
    'momo' => [
        'access_key'   => env('MOMO_ACCESS_KEY', 'F8BBA842ECF85'),
        'secret_key'   => env('MOMO_SECRET_KEY', 'K951B6PE1waDMi640xX08PD3vg6EkVlz'),
        'partner_code' => env('MOMO_PARTNER_CODE', 'MOMO'),
        'redirect_url' => env('MOMO_REDIRECT_URL', 'http://localhost:8000/api/group-orders/payments/momo/return'), // optional
        'ipn_url'      => env('MOMO_IPN_URL', 'http://localhost:8000/api/group-orders/payments/momo/ipn'),        // ← BE
        'request_type' => env('MOMO_REQUEST_TYPE', 'payWithMethod'),
        'hostname'     => env('MOMO_HOSTNAME', 'test-payment.momo.vn'),
        'endpoint'     => env('MOMO_ENDPOINT', '/v2/gateway/api/create'),
    ],
];
