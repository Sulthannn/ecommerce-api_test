<?php

namespace App\Http\Controllers;

use App\Models\{Order, Payment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class PaymentWebhookController extends Controller
{
    public function xendit(Request $request)
    {
        $token = config('payment.xendit.callback_token');
        $headerToken = $request->header('x-callback-token');
        if ($token && $token !== $headerToken) {
            return response()->json(['pesan' => 'Token webhook tidak valid'], 403);
        }

        $payload = $request->all();
        Log::info('Webhook Xendit diterima', $payload);

        $externalId = $payload['external_id'] ?? null;
        $status = $payload['status'] ?? null;
        if (!$externalId) {
            return response()->json(['pesan' => 'external_id kosong'], 422);
        }

        $order = Order::where('kode', $externalId)->first();
        if (!$order) {
            return response()->json(['pesan' => 'Order tidak ditemukan'], 404);
        }

        if ($status === 'PAID') {
            $order->update([
                'status' => 'dibayar',
                'dibayar_pada' => now(),
            ]);
            $payment = $order->payments()->latest()->first();
            if ($payment) {
                $payment->update([
                    'berhasil_pada' => now(),
                    'payload' => $payload,
                ]);
            } else {
                Payment::create([
                    'order_id' => $order->id,
                    'provider' => 'xendit',
                    'tipe' => 'invoice',
                    'referensi' => $payload['id'] ?? null,
                    'jumlah' => $order->total_harga,
                    'payload' => $payload,
                    'berhasil_pada' => now(),
                ]);
            }
        }

        return response()->json(['pesan' => 'ok']);
    }
}
