<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate(
                [
                    'password' => 'required',
                    'username' => 'required',
                ]
            );
            $user = User::query();

            $creadentials = request(['username', 'password']);
            if (!Auth::attempt($creadentials)) {
                return ResponseFormatter::error([
                    'message' => "Username atau password tidak sesuai",
                ], 'Unauthorized', 500);
            }

            $user = User::where('username', $request->username)->first();
            if ($request->token_notification) {
                $user->token_notification = $request->token_notification;
                $user->update();
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;


            return ResponseFormatter::success(
                [
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user,
                ],
                'Authenticated'
            );
        } catch (Exception $error) {
            $message = 'Authentication Failed';
            if (!$request->password) {
                $message = "Harap memasukan password";
            }
            return ResponseFormatter::error([
                'message' => $message,
                'error' => $error,
            ],  'Authentication Failed', 500);
        }
    }

    public function register(Request $request)
    {
        try {

            // $user = User::query();
            $request->validate([
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'password' => ['required', 'string'],
            ]);
            // dd($request->nama);
            $user =  User::create([
                'name'  => $request->name,
                'email' => $request->name,
                'username' => $request->username,
                'role' => $request->role,
                'no_hp' => $request->no_hp,
                'date_of_birth' => $request->date_of_birth,
                'password' => Hash::make($request->password),
            ]);
            $user = User::where('username', $request->username)->first();

            $tokenResult = $user->createToken('auth_token')->plainTextToken;


            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Authenticated');
        } catch (Exception $error) {
            $userUserPhone = User::where('username', $request->username)->first();

            if ($userUserPhone) {
                return ResponseFormatter::error([
                    'message' => "Username telah digunakan",
                    'error' => $error,
                ], 'Something went wrong', 500);
            }

            return ResponseFormatter::error([
                'message' => "Something went wrong",
                'error' => $error,
            ], 'Authentication Failed', 500);
        }
    }

    public function logout(Request $request)
    {

        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'Token Revoked');
    }



    public function checkUsername(Request $request)
    {
        try {
            $username = $request->username;
            $user = User::where('username',  $username)->get();
            if ($user->isNotEmpty()) {
                return ResponseFormatter::error([
                    'message' => "Username telah digunakan",
                ],  'Username Not Ready', 500);
            } else {
                return ResponseFormatter::success(
                    [
                        'message' => "Username Tersedia",
                    ],
                    'Username Ready'
                );
            }
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => "terjadi Kesalahan",
                'error' => $error,
            ],  'Get failed', 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {

            $user = User::where('username', $request->username)->first();
            if ($request->password) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
                return ResponseFormatter::success(
                    true,
                    'Password Berhasil Diubah',
                );
            }
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => "Ubah Passowrd gagal",
                'error' => $error,
            ],  'Update Failed', 500);
        }
    }
}
