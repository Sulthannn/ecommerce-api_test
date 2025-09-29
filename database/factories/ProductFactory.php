<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $wordsPool = ['Alpha','Beta','Gamma','Delta','Epsilon','Sigma','Prime','Nova','Ultra','Core'];
            shuffle($wordsPool);
            $nama = implode(' ', array_slice($wordsPool, 0, 3));
            $deskripsi = 'Produk ' . $nama . ' generik untuk keperluan demo.';

            return [
                'nama' => $nama,
                'deskripsi' => $deskripsi,
                'stok' => random_int(10, 100),
                'harga' => random_int(10000, 250000),
            ];
    }
}
