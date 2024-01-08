<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('authToken')->plainTextToken;
                return response()->json([
                    'isLoggedIn' => true,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->full_name,
                        'email' => $user->email,
                        'phoneNumber' => $user->mobile,
                        'about' => $user->about,
                        'language' => $user->language,
                        'timezone' => $user->timezone,
                        'created_at' => $user->created_at,
                        'deleted_at' => $user->deleted_at,
                        'updated_at' => $user->updated_at
                    ],
                    'accessToken' => $token
                ], 200);
            }

            return response()->json(['message' => 'Invalid credentials.'], 401);
        } catch (\Exception $error) {
            Log::channel('server_error')->error('Lỗi đăng xuất', $request->all());
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in Login',
                'error' => $error->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::guard('web')->logout();
            Session::flush();
            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (\Exception $error) {
            Log::channel('server_error')->error('Lỗi đăng xuất', $error->getMessage());
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in Logout',
                'error' => $error->getMessage(),
            ], 500);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()

            ], 422);
        } else {
            try {
                $user = new User([
                    'full_name' => $request->input('full_name'),
                    'email' => $request->input('email'),
                    'password' => bcrypt($request->input('password')),
                    'timezone' => 'asia/ho chi minh',
                    'role_id' => 1,
                    'role_name' => 'user',
                    'created_at' => now()->getTimestamp(),
                    'updated_at' => now()->getTimestamp(),
                ]);
                $user->save();
                Auth::login($user);
                return response()->json([
                    'status' => 'success',
                ], 200);
            } catch (\Exception $e) {
                if ($e instanceof \Illuminate\Http\Client\RequestException && $e->response) {
                    // If there's a response, decode its JSON content
                    $body = $e->response->json();
                    $statusCode = $e->response->status();
                } else {
                    // If there's no response, create a generic error message
                    $body = ['error' => $e->getMessage()];
                    $statusCode = $e->getCode() ?: 500; // Default to 500 if no code is available
                }
                Log::channel('server_error')->error('Lỗi đăng ký', $request->all);
                return response()->json([
                    'body' => $body,
                    'statusCode' => $statusCode
                ]);
            }
        }
    }
}
