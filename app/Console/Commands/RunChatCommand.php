<?php

namespace App\Console\Commands;

use App\User;
use BasementChat\Basement\Contracts\SendPrivateMessage;
use BasementChat\Basement\Data\PrivateMessageData;
use BasementChat\Basement\Enums\MessageType;
use BasementChat\Basement\Http\Controllers\Api\CurrentlyTypingController;
use BasementChat\Basement\Models\PrivateMessage;
use Illuminate\Console\Command;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class RunChatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run chatbot reply server';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        while (true) {
            $needle = PrivateMessage::notHandled()->where('receiver_id', 1)->first();
            if (is_null($needle)) {
                $this->info('No message to handle');
                usleep(10000);
                continue;
            }

            call_user_func(new CurrentlyTypingController, $needle->sender, User::find(1));
            // sleep(2);

            $senderId = 1;

            $answer = Http::withBody('{
                "user_id": "1",
                "session_id": "1",
                "mode": "course",
                "query": "' . $needle->value . '"
            }', 'application/json')
                ->withHeaders([
                    'accept' => '*/*',
                ])
                ->post('http://34.16.32.114:9000/chat');

            $value = $answer->body();
            // dd($value);

            \Log::info('To http://34.16.32.114:9000/chat with {
                "user_id": "1",
                "session_id": "1",
                "mode": "course",
                "query": "' . $needle->value . '"
            }');

            $message = app()->make(SendPrivateMessage::class)->send(new PrivateMessageData(
                receiver_id: $needle->sender->id,
                sender_id  : (int)$senderId,
                type       : MessageType::text(),
                value      : $value,
            ));

            // return (new JsonResource($message))->response()->setStatusCode(Response::HTTP_CREATED);
            $needle->handle_at = now();
            $needle->save();
            $this->info('Handled message: ' . $needle->id);
            usleep(10000);
        }
    }
}
