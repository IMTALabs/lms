<?php

namespace App\Http\Controllers\Api\English;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReadingController extends Controller
{
    public function reading(Request $request)
    {
        $user_id = Auth::user()->id;
        $mode = $request->input('mode') ?? '';
        $topic = $request->input('topic') ?? '';
        $paragraph = $request->input('paragraph') ?? '';
        $validator = Validator::make($request->all(), [
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
                    'id' => (string)$user_id,
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
                ],200);
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
                Log::channel('server_error')->error('Lỗi Server', $body);

                return response()->json([
                    'statusCode' => $statusCode,
                    'body' => $body,
                ], $statusCode);
            }
        }
    }
}
