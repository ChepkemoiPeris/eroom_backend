<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
 
    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }
  

    public function broadcastOn()
    {
        $senderId = $this->message->sender_id;
        $receiverId = $this->message->receiver_id;

        // // Determine groupChatId
        // $groupChatId = $senderId <= $receiverId
        //     ? "{$senderId}-{$receiverId}"
        //     : "{$receiverId}-{$senderId}"; 
        // \Log::info("Broadcasting on channel: chat-channel.{$groupChatId}");

        return new PrivateChannel("chat-room");
    }


    public function broadcastAs()
    {
        return 'NewMessageEvent';
    }
 

    public function broadcastWith()
        {
            $broadcastData = [
                'id' => $this->message->id,
                'sender_id' => $this->message->sender_id,
                'receiver_id' => $this->message->receiver_id,
                'content' => $this->message->content,
                'status' => $this->message->status,
                'reply_to' => $this->message->reply_to,
                'type' => $this->message->type,
                'created_at' => $this->message->created_at,
                'updated_at' => $this->message->updated_at,
            ];
             
            return $broadcastData;
        }

}
 