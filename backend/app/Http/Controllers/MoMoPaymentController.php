<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoMoPaymentController extends Controller
{
    public function createPayment(Request $request)
    {
        $amount = $request->input('amount', 500000);
        $orderInfo = $request->input('orderInfo', '123456');

        $config = config('payment.momo');
        $orderId = $config['partner_code'] . time();
        $requestId = $orderId;
        $extraData = '';
        $autoCapture = true;
        $lang = 'vi';

        // Build raw signature
        $rawSignature = "accessKey={$config['access_key']}&amount={$amount}&extraData={$extraData}&ipnUrl={$config['ipn_url']}&orderId={$orderId}&orderInfo={$orderInfo}&partnerCode={$config['partner_code']}&redirectUrl={$config['redirect_url']}&requestId={$requestId}&requestType={$config['request_type']}";

        $signature = hash_hmac("sha256", $rawSignature, $config['secret_key']);

        $body = [
            'partnerCode' => $config['partner_code'],
            'partnerName' => 'Test',
            'storeId' => 'MomoTestStore',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $config['redirect_url'],
            'ipnUrl' => $config['ipn_url'],
            'lang' => $lang,
            'requestType' => $config['request_type'],
            'autoCapture' => $autoCapture,
            'extraData' => $extraData,
            'orderGroupId' => '',
            'signature' => $signature,
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://{$config['hostname']}{$config['endpoint']}", $body);

            return response()->json([
                'message' => 'MoMo payment request created successfully',
                'data' => $response->json(),
            ], 201);
        } catch (\Exception $e) {
            Log::error('MoMo Payment Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error creating MoMo payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function handleResult(Request $request)
    {
        $data = $request->all();
        $config = config('payment.momo');

        if (empty($data)) {
            return response()->json(['message' => 'Empty MoMo result'], 400);
        }

        $rawSignature = "accessKey={$config['access_key']}&amount={$data['amount']}&extraData={$data['extraData']}&message={$data['message']}&orderId={$data['orderId']}&orderInfo={$data['orderInfo']}&orderType={$data['orderType']}&partnerCode={$data['partnerCode']}&payType={$data['payType']}&requestId={$data['requestId']}&responseTime={$data['responseTime']}&resultCode={$data['resultCode']}&transId={$data['transId']}";

        $expectedSignature = hash_hmac("sha256", $rawSignature, $config['secret_key']);

        if ($expectedSignature !== $data['signature']) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $paymentStatus = $data['resultCode'] == 0 ? 'SUCCESS' : 'FAILED';

        // TODO: Cập nhật trạng thái đơn hàng trong DB
        // Order::where('order_id', $data['orderId'])->update([...]);

        return response()->json([
            'message' => 'MoMo payment result received and verified',
            'status' => $paymentStatus,
            'orderId' => $data['orderId'],
            'transactionId' => $data['transId'],
            'amount' => $data['amount'],
            'resultCode' => $data['resultCode'],
            'momoMessage' => urldecode($data['message'] ?? ''),
        ]);
    }
}
