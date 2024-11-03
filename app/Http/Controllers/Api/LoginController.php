<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * POST /api/login
     *
     * Endpoint ini digunakan untuk autentikasi pengguna dan menghasilkan token jika login berhasil.
     *
     * @access Public
     * @param string $email Email pengguna yang akan login.
     * @param string $password Kata sandi pengguna.
     * 
     * @response 200 OK - Login berhasil, mengembalikan token autentikasi.
     * @response 401 Unauthorized - Kredensial tidak valid, login gagal.
     * @response 422 Unprocessable Entity - Validasi input gagal (email atau password tidak diisi).
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        //set validation
        $validator = Validator::make($request->all(), [
            'email'     => 'required',
            'password'  => 'required'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //get credentials from request
        $credentials = $request->only('email', 'password');

        //if auth failed
        if(!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password Anda salah'
            ], 401);
        }

        // if auth success
        return response()->json([
            'success' => true,
            'user'    => auth()->guard('api')->user(),    
            'token'   => $token   
        ], 200);
    }
}