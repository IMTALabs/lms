<?php

namespace App\Jobs;

use BasementChat\Basement\Contracts\SendPrivateMessage;
use BasementChat\Basement\Data\PrivateMessageData;
use BasementChat\Basement\Enums\MessageType;
use BasementChat\Basement\Models\PrivateMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public int $messageId
    )
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $message = PrivateMessage::find($this->messageId);

        $senderId   = 1;
        $receiverId = $message->sender_id;

        $query  = http_build_query([
            'user_id' => $receiverId,
            'session_id' => $receiverId,
            'mode' => 'course',
            'query' => $message->value,
        ]);

        $answer = Http::withHeaders([
            'accept' => '*/*',
        ])
            ->get('http://34.16.32.114:9000/chat?' . $query);

        $value = $answer->body();

        // Extract JSON part from the answer
        preg_match('/\{.*\}/', $value, $matches);
        $value = preg_replace('/\{.*\}/', '', $value);

        try {
            $replyMessage = app()->make(SendPrivateMessage::class)->send(new PrivateMessageData(
                receiver_id: $receiverId,
                sender_id  : $senderId,
                type       : MessageType::text(),
                value      : Str::markdown($value),
            ));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
