<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\MessageSent; 

class MessageController extends Controller
{
    public function getMessages(Request $request, $peerId)
    { 
        $userId = auth()->id();
 
        $limit = $request->input('limit', 20);  
        $offset = $request->input('offset', 0);  
 
        $messages = Message::where(function ($query) use ($userId, $peerId) {
                $query->where('sender_id', $userId)->where('receiver_id', $peerId);
            })
            ->orWhere(function ($query) use ($userId, $peerId) {
                $query->where('sender_id', $peerId)->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'DESC') 
            ->skip($offset) 
            ->take($limit) 
            ->get();

        return response()->json($messages);
    }


     public function getMessage(Request $request, $messageId)
    {
        $message = Message::find($messageId);

        return response()->json($message);
    }

    public function sendMessage(Request $request)
    {  
        $validatedData = $request->validate([
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string',
            'reply_to' => 'nullable',
            'type' => 'nullable',
        ]);

        $message = Message::create([
            'sender_id' => $validatedData['sender_id'],
            'receiver_id' => $validatedData['receiver_id'],
            'content' => $validatedData['content'],
            'reply_to' => $validatedData['reply_to'] ?? null,
            'type'=> $validatedData['type'] ?? 0,
            'status' => 'unread',
        ]);

        try {
         

            //  broadcast(new MessageSent($message));
             event(new MessageSent($message));
            return response()->json([
                'message' => $message,
                'event_fired' => true,
            ]);
        } catch (\Exception $e) {
            \Log::error('Broadcast failed: ' . $e->getMessage(), ['trace' => $e->getTrace()]);
            return response()->json([
                'message' => $message,
                'event_fired' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getConversations($userId)
    {
        // Fetch all users who have had a conversation with the given user
        $conversations = \DB::table('messages')
            ->select(
                \DB::raw('
                    CASE 
                        WHEN sender_id = ? THEN receiver_id 
                        ELSE sender_id 
                    END as other_user_id,
                    MAX(created_at) as last_message_time
                ', [$userId])
            )
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->groupBy('other_user_id')
            ->orderBy('last_message_time', 'desc')
            ->get();

        // Fetch latest message for each conversation
        $conversations = $conversations->map(function ($conversation) use ($userId) {
            $latestMessage = \DB::table('messages')
                ->where(function ($query) use ($userId, $conversation) {
                    $query->where('sender_id', $userId)
                        ->where('receiver_id', $conversation->other_user_id);
                })
                ->orWhere(function ($query) use ($userId, $conversation) {
                    $query->where('receiver_id', $userId)
                        ->where('sender_id', $conversation->other_user_id);
                })
                ->latest('created_at')
                ->first();

            return [
                'user_id' => $conversation->other_user_id,
                'latest_message' => $latestMessage->content ?? '',
                'timestamp' => $latestMessage->created_at ?? null,
            ];
        });

        return response()->json($conversations);
    }

     
    public function markAsRead($message_id)
    {
        $message = Message::find($message_id);

        if ($message && $message->receiver_id == auth()->id()) {
            $message->markAsRead();
            return response()->json(['status' => 'Message marked as read']);
        }

        return response()->json(['error' => 'Message not found or unauthorized'], 404);
    }
    public function getUsers()
    {
        $loggedInUserId = auth()->id();

        $chattedUsers = DB::table('users')
            ->join('messages', function ($join) use ($loggedInUserId) {
                $join->on('users.id', '=', 'messages.sender_id')
                    ->orOn('users.id', '=', 'messages.receiver_id');
            })
            ->where(function ($query) use ($loggedInUserId) {
                $query->where('messages.sender_id', $loggedInUserId)
                    ->orWhere('messages.receiver_id', $loggedInUserId);
            })
            ->where('users.id', '!=', $loggedInUserId) // Exclude logged-in user
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                'users.photo',
                DB::raw('MAX(messages.created_at) as last_message_time')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('last_message_time', 'desc')
            ->get();

        // Now, fetch the last message content for each user
        $chattedUsers = $chattedUsers->map(function ($user) {
            $lastMessage = DB::table('messages')
                ->where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id)
                ->where('created_at', $user->last_message_time)
                ->first();

            // If no message is found, assign null
            $user->last_message = $lastMessage ? $lastMessage->content : null;

            return $user;
        });

        return $chattedUsers;
    }

   public function uploadFile(Request $request)
    {
        // Validate the file
        $validatedData = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,docx,mp3,aac,audio/mp4,mp4|max:5120',
        ]);

        // Store the file in the 'public' disk
        // $path = $request->file('file')->store('uploads', 'public');
        $file = $request->file('file');
        $fileExtension = $file->getClientOriginalExtension();
        
        // Determine the storage folder based on the file type
        if (in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
            // Image file
            $filePath = $file->store('message_files/images', 'public');
        } elseif (in_array($fileExtension, ['aac','mp3', 'mp4'])) {
            // Audio/Video file
            $filePath = $file->store('message_files/audio', 'public');
        } else {
            // Other types (PDF, etc.) can go in a general folder
            $filePath = $file->store('message_files/others', 'public');
        }
       
        return response()->json([
            'success' => true, 
            'file_path' =>$filePath
        ]);
    }

}
