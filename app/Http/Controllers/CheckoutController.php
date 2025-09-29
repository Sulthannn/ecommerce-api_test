<?php

namespace App\Http\Controllers;

use App\Models\{Order, OrderItem, Product, Payment};
use App\Services\Payments\XenditPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function checkout(Request $request, XenditPaymentService $paymentService)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.jumlah' => 'required|integer|min:1'
        ]);

        $user = $request->user();

        $order = DB::transaction(function() use ($data, $user, $paymentService) {
            $total = 0;
            $order = Order::create([
                'user_id' => $user->id,
                'kode' => 'ORD-' . strtoupper(Str::random(10)),
                'total_harga' => 0,
                'status' => 'menunggu_pembayaran'
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                if ($product->stok < $item['jumlah']) {
                    abort(422, 'Stok tidak cukup untuk produk '.$product->nama);
                }
                $product->decrement('stok', $item['jumlah']);
                $subtotal = $product->harga * $item['jumlah'];
                $total += $subtotal;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'harga_satuan' => $product->harga,
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $subtotal
                ]);
            }

            $order->update(['total_harga' => $total]);

            $invoice = $paymentService->buatInvoice($order);

            $order->update([
                'provider' => 'xendit',
                'provider_reference' => $invoice['id'] ?? null,
            ]);

            Payment::create([
                'order_id' => $order->id,
                'provider' => 'xendit',
                'tipe' => 'invoice',
                'referensi' => $invoice['id'] ?? null,
                'jumlah' => $order->total_harga,
                'payload' => $invoice,
            ]);

            return [$order, $invoice];
        });

        [$orderModel, $invoice] = $order;

        return response()->json([
            'pesan' => 'Order dibuat, silakan lanjutkan pembayaran',
            'data' => [
                'order' => $orderModel->load('items'),
                'invoice' => $invoice
            ]
        ], 201);
    }
}
