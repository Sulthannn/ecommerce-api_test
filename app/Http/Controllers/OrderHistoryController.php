<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderHistoryController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->with('items.product')->latest()->paginate(10);
        return response()->json(['data' => $orders]);
    }

    public function show(Request $request, $kode)
    {
        $order = $request->user()->orders()->where('kode', $kode)->with('items.product','payments')->firstOrFail();
        return response()->json(['data' => $order]);
    }
}