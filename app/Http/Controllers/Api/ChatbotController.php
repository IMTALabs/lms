<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            Log::debug($question);
            return response()->stream(function () use ($question) {
                $start = microtime(true);
                $stream = OpenAI::completions()->createStreamed([
                    'user_id' => Auth::id(),
                    'session_id' => Auth::id(),
                    'mode' => "course",
                    'query' => $question,
                ]);

                foreach ($stream as $response) {
                    $text = $response->choices[0]->text;
                    if (connection_aborted()) {
                        break;
                    }

                    echo "event: message\n";
                    if (is_array($text)) {
                        foreach ($text as $line) {
                            echo 'data: ' . $line;
                            echo "\n";
                        }
                    } else {
                        echo 'data: ' . $text;
                    }
                    echo "\n\n";
                    ob_flush();
                    flush();
                }

                // Manual close
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
        }
    }
}
