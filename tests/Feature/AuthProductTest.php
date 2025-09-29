<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_register_login_and_list_products(): void
    {
        $accessKey = 'testing-key';
        config(['app.api_access_key' => $accessKey]);

        $headers = ['X-Access-Key' => $accessKey, 'Accept' => 'application/json'];

        $register = $this->postJson('/api/auth/daftar', [
            'nama' => 'Tester',
            'email' => 'tester@example.com',
            'password' => 'secret123'
        ], $headers);
    $register->assertStatus(201);

        $login = $this->postJson('/api/auth/masuk', [
            'email' => 'tester@example.com',
            'password' => 'secret123'
        ], $headers);
    $login->assertOk();
        $token = $login->json('data.token');
        $this->assertNotEmpty($token, 'Token kosong pada response login: '.$login->getContent());

        $list = $this->getJson('/api/produk', $headers);
        $list->assertOk();

        $logout = $this->postJson('/api/auth/keluar', [], array_merge($headers, [
            'Authorization' => 'Bearer '.$token
        ]));
        $logout->assertOk();

        $profil = $this->getJson('/api/auth/profil', array_merge($headers, [
            'Authorization' => 'Bearer '.$token
        ]));
        $profil->assertStatus(401);
    }
}