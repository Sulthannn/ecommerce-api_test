<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $data['nama'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        $token = $user->createToken('akses-api')->plainTextToken;

        return response()->json([
            'pesan' => 'Registrasi berhasil',
            'data' => [
                'pengguna' => $user,
                'token' => $token
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial tidak sesuai.']
            ]);
        }

        $token = $user->createToken('akses-api')->plainTextToken;

        return response()->json([
            'pesan' => 'Login berhasil',
            'data' => [
                'pengguna' => $user,
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $tokenId = null;
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $raw = substr($authHeader, 7);
            $parts = explode('|', $raw, 2);
            if (count($parts) === 2) {
                $tokenId = $parts[0];
                DB::table('personal_access_tokens')->where('id', $tokenId)->delete();
            }
        }

        if ($tokenId && $request->user()) {
            $request->user()->tokens()->where('id', $tokenId)->delete();
        }

        return response()->json(['pesan' => 'Token dicabut']);
    }

    public function profile(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['pesan' => 'Tidak terautentik'], 401);
        }
        $raw = substr($authHeader, 7);
        $parts = explode('|', $raw, 2);
        if (count($parts) !== 2) {
            return response()->json(['pesan' => 'Tidak terautentik'], 401);
        }
        $tokenId = $parts[0];
        $exists = DB::table('personal_access_tokens')->where('id', $tokenId)->exists();
        if (!$exists) {
            return response()->json(['pesan' => 'Tidak terautentik'], 401);
        }
        $user = $request->user();
        if (!$user) {
            return response()->json(['pesan' => 'Tidak terautentik'], 401);
        }
        return response()->json(['data' => $user]);
    }
}