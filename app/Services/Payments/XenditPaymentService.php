<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class XenditPaymentService
{
    public function buatInvoice(Order $order): array
    {
        $config = config('payment.xendit');

        $payload = [
            'external_id' => $order->kode,
            'amount' => $order->total_harga,
            'payer_email' => $order->user->email,
            'description' => 'Pembayaran pesanan #' . $order->kode,
            'invoice_duration' => 3600,
            'success_redirect_url' => config('app.url') . '/success',
            'failure_redirect_url' => config('app.url') . '/failed',
        ];

        if (empty($config['api_key'])) {
            return [
                'dummy' => true,
                'invoice_url' => 'https://checkout.dummy.local/invoice/'.Str::uuid(),
                'id' => 'inv-dummy-'.Str::random(8),
                'status' => 'PENDING'
            ];
        }

        $response = Http::withBasicAuth($config['api_key'], '')
            ->post(rtrim($config['base_url'],'/').'/v2/invoices', $payload)
            ->throw()
            ->json();

        return $response;
    }
}
