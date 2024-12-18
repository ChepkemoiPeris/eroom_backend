<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Ably\AblyRest;
use App\Events\MessageSent;
use App\Models\User;

class MessageSentListener
{
       public function handle(MessageSent $event)
    {
        $message = $event->message;

        // Retrieve the receiver's user information (assuming you have a User model with a device token)
        $receiver = User::find($message->receiver_id);
        $sender = User::find($message->sender_id);

        // Check if the receiver has a device token for push notifications
        if ($receiver && $receiver->device_id) { 
            $this->sendPushNotification($receiver->device_id, $message,$sender);
        }
        
    }

    protected function sendPushNotification($deviceToken, $message,$sender)
    {
        // Set up the Ably client
        $apiKey = env('ABLY_KEY'); 
        $ably = new AblyRest($apiKey);
 

        $recipient = [
            'clientId' => $deviceToken
        ]; 
        $data = [
            'notification' => [
                'title' => 'New Message from ' . $sender->name,
                'body' => $message->content,  
            ], 
            'data' => [ 
            'userId' => "{$sender->id}",
            'senderName' => "{$sender->name}",
            'contactNumber' => "{$sender->phone}",
            'photo' => "{$sender->photo}",
            'idTo'=> "{$message->receiver_id}"
        ],
        ];


        $ably->push->admin->publish($recipient, $data);
 
     
      
     
    }
}
