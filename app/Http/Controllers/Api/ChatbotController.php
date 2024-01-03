<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        try {
            $question = $request->query('query');
            return response()->stream(function () use ($question) {
                $stream = OpenAI::completions()->createStreamed([
                    'user_id' => "1",
                    'session_id' => "1",
                    'mode' => "course",
                    'query' => $question,
                ]);

                foreach ($stream as $response) {
                    $text = $response->choices[0]->text;
                    // $text = str_replace("\r", "\n", $text);
                    \Log::info($text);
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
