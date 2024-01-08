<?php

namespace App\Http\Controllers\Api\English;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WritingController extends Controller
{
    public function writing_gen_instruction(Request $request)
    {
        $user_id = Auth::user()->id;
        $topic = $request->input('topic');
        $validator = Validator::make($request->all(), [
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
                    'id' => (string)$user_id,
                    'topic' => $topic,
                ]);

                // Lấy mã trạng thái của response
                $statusCode = $response->getStatusCode();

                // Decode nội dung JSON từ response
                $body = json_decode($response->getBody(), true);

                // Hiển thị nội dung để kiểm tra
                return response()->json([
                    'data' => $body,
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
                Log::channel('server_error')->error('Lỗi server', $body);
                return response()->json([
                    'statusCode' => $statusCode,
                    'body' => $body,
                ], $statusCode);
            }
        }
    }

    public function evalue(Request $request)
    {
        $user_id = Auth::user()->id;
        $instruction = $request->input('instruction');
        $submission = $request->input('submission');
        $validator = Validator::make($request->all(), [
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
                    'id' => (string)$user_id,
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
                Log::channel('server_error')->error('Lỗi server', $body);
                return response()->json([
                    'statusCode' => $statusCode,
                    'body' => $body,
                ], $statusCode);
            }
        }
    }
}
