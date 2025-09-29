<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $data = Product::query()->paginate(10);
        return response()->json(['data' => $data]);
    }

    public function show(Product $product)
    {
        return response()->json(['data' => $product]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Product::class);
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'stok' => 'required|integer|min:0',
            'harga' => 'required|integer|min:0'
        ]);

        $product = Product::create($data);
        return response()->json(['pesan' => 'Produk dibuat','data' => $product], 201);
    }
}
