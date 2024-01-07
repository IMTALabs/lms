<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AiController extends Controller
{
    public function route_landing_page()
    {
        // các route của landing page
        $array = ['Home', 'Listening', 'Writing', 'Reading', 'Speaking'];
        return response()->json($array);
    }
    public function listening()
    {
        set_time_limit(60);
        $listeningLink = request()->input('listen_link');
        $user_id = Auth::user()->id;
        // dd($user_id);
        $validator = Validator::make(\request()->all(), [
            'listen_link' => 'required|url'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()

            ], 422);
        } else {
            preg_match('/\?v=(.*)/', $listeningLink, $matches);
            $youtubeId = $matches[1];
            $embedCode = '
          <iframe width="100%" height="100%" src="https://www.youtube.com/embed/' . $youtubeId . '" title="How to convert Figma Design into Flutter Code | DhiWise.com" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
          ';
            try {

                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post('http://34.16.32.114:8150/gen_quizz', [
                    'id' => $user_id,
                    'url' => $listeningLink,
                    'num_quizz' => 2,
                ]);

                // Lấy mã trạng thái của response
                $statusCode = $response->getStatusCode();

                // Decode nội dung JSON từ response
                $body = json_decode($response->getBody(), true);

                // Hiển thị nội dung để kiểm tra
                return response()->json([
                    'data' => [
                        'body' => $body,
                        'link' => $listeningLink,
                    ]
                ]);
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

                return response()->json([
                    'statusCode' => $statusCode,
                    'body' => $body,
                ], $statusCode);
            }
        }
        return response()->json([
            'error' => 'Không có giá trị từ query parameter \'listen_link\'',
        ]);
    }
    public function writing_gen_instruction()
    {
        $user_id = Auth::user()->id;
        $topic = \request()->input('topic');
        $validator = Validator::make(\request()->all(), [
            'topic' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()

            ], 422);
        } else {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post('http://34.16.32.114:8300/gen_instruction', [
                    'id' => $user_id,
                    'topic' => $topic,
                ]);

                // Lấy mã trạng thái của response
                $statusCode = $response->getStatusCode();

                // Decode nội dung JSON từ response
                $body = json_decode($response->getBody(), true);

                // Hiển thị nội dung để kiểm tra
                return response()->json([
                    'data' => $body,
                ]);
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

                return response()->json([
                    'statusCode' => $statusCode,
                    'body' => $body,
                ], $statusCode);
            }
        }
    }
    public function evalue()
    {
        $user_id = Auth::user()->id;
        $instruction = \request()->input('instruction');
        $submission = \request()->input('submission');
        $validator = Validator::make(\request()->all(), [
            'instruction' => 'required',
            'submission' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()

            ], 422);
        } else {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post('http://34.16.32.114:8300/evaluate', [
                    'id' => $user_id,
                    'submission' => $submission,
                    'instruction' => $instruction
                ]);

                // Lấy mã trạng thái của response
                $statusCode = $response->getStatusCode();

                // Decode nội dung JSON từ response
                $body = json_decode($response->getBody(), true);

                // Hiển thị nội dung để kiểm tra
                return response()->json([
                    'data' => $body,
                ]);
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

                return response()->json([
                    'statusCode' => $statusCode,
                    'body' => $body,
                ], $statusCode);
            }
        }
    }
    public function reading(Request $request)
    {
        //         "mode": "gen_topic",
        //   "topic": "Your Family",
        //   "paragraph": "",
        //   "num_quizz": 10

        $user_id = Auth::user()->id;
        $mode = $request->input('mode') == null ? '' : $request->input('mode');
        $topic = $request->input('topic') == null ? '' : $request->input('topic');
        $paragraph = $request->input('paragraph') == null ? '' : $request->input('paragraph');
        $validator = Validator::make(\request()->all(), [
            'mode' => 'required|in:gen_topic,no_gen_topic',
            'topic' => 'required_without:paragraph',
            'paragraph' => 'required_without:topic',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()

            ], 422);
        } else {
            // dd($request);
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post('http://34.16.32.114:8200/gen_quizz', [
                    'id' => $user_id,
                    "mode" => $mode,
                    "topic" => $topic,
                    "paragraph" => $paragraph,
                    "num_quizz" => 2
                ]);

                // Lấy mã trạng thái của response
                $statusCode = $response->getStatusCode();

                // Decode nội dung JSON từ response
                $body = json_decode($response->getBody(), true);

                // Hiển thị nội dung để kiểm tra
                return response()->json([
                    'data' => $body,
                ]);
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

                return response()->json([
                    'statusCode' => $statusCode,
                    'body' => $body,
                ], $statusCode);
            }
        }
    }
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                Auth::login($user);
                $token = $user->createToken('authToken')->plainTextToken;
                return response()->json([
                    'isLoggedIn' => true,
                    // 'user' => $user,
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
                ]);
            }

            return response()->json(['message' => 'Invalid credentials.'], 401);
        } catch (\Exception $error) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in Login',
                'error' => $error->getMessage(),
            ]);
        }
    }
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            

            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $error) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Error in Logout',
                'error' => $error->getMessage(),
            ]);
        }
    }
}
