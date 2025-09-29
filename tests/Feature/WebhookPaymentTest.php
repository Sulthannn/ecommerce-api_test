<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_checkout_then_webhook_marks_order_paid(): void
    {
        config(['app.api_access_key' => 'testing-key']);
        config(['payment.xendit.callback_token' => 'hooktoken']);

        $reg = $this->postJson('/api/auth/daftar', [
            'nama' => 'Tester',
            'email' => 'tester2@example.com',
            'password' => 'secret123'
        ], ['X-Access-Key' => 'testing-key','Accept'=>'application/json']);
    $reg->assertStatus(201);

        $login = $this->postJson('/api/auth/masuk', [
            'email' => 'tester2@example.com',
            'password' => 'secret123'
        ], ['X-Access-Key' => 'testing-key','Accept'=>'application/json']);
        $token = $login->json('data.token');
        $this->assertNotEmpty($token, 'Token login kosong');

        $productId = Product::query()->first()->id;

        $checkout = $this->postJson('/api/checkout', [
            'items' => [
                ['product_id' => $productId, 'jumlah' => 1]
            ]
        ], [
            'X-Access-Key' => 'testing-key',
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json'
        ]);
    $checkout->assertStatus(201);

        $kode = $checkout->json('data.order.kode');
        $this->assertNotEmpty($kode, 'Kode order kosong');

        $webhook = $this->postJson('/api/webhook/xendit', [
            'external_id' => $kode,
            'status' => 'PAID',
            'id' => 'inv-simulated-1'
        ], [
            'x-callback-token' => 'hooktoken',
            'Accept' => 'application/json'
        ]);
    $webhook->assertOk();

        $detail = $this->getJson('/api/riwayat/'.$kode, [
            'X-Access-Key' => 'testing-key',
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json'
        ]);
        $detail->assertOk();
        $this->assertEquals('dibayar', $detail->json('data.status'));
    }
}