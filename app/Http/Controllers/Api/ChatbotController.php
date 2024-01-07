<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $question = $request->get('query');
            return response()->stream(function () use ($question) {
                $stream = OpenAI::completions()->createStreamed([
                    'user_id' => Auth::id(),
                    'session_id' => Auth::id(),
                    'mode' => "course",
                    'query' => $question,
                ]);

                $start = microtime(true);
                foreach ($stream as $response) {
                    if (microtime(true) - $start > 60) {
                        break;
                    }

                    $text = $response->choices[0]->text;
                    if (connection_aborted()) {
                        break;
                    }

                    echo $text;
                    echo "\n\n";
                    ob_flush();
                    flush();
                }

                // Manual close
                echo "id: 1\n";
                echo "event: message\n";
                echo 'data: <END_STREAM_SSE>';
                echo "\n\n";
                ob_flush();
                flush();

                Log::channel('chat_request')->info('✅ Success', [
                    'user' => \Auth::id(),
                    'question' => $question,
                    'time' => microtime(true) - $start,
                ]);
            }, 200, [
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
                'Content-Type' => 'text/event-stream',
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), $th->getTrace());
            abort(404, 'Something went wrong.');
        }
    }

    public function new()
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->withBody(json_encode([
                    'user_id' => (string) Auth::id(),
                    'session_id' => (string) Auth::id(),
                ]), 'application/json')
                ->post('http://34.16.32.114:9000/clear');

            if ($response->failed()) {
                abort(500, 'Something went wrong.');
            }

            return response()->json([
                'message' => 'success',
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), $th->getTrace());
            abort(404, 'Something went wrong.');
        }
    }
}
