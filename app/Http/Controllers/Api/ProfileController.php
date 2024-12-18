<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\Notification;
use App\Models\Message;
 
   
class ProfileController extends Controller
{
     public function getCounts(Request $request)
    {
        $userId = $request->user()->id;

        // Get the count of unread notifications
        $notificationsCount = Notification::where('user_id', $userId)->where('is_read', 0)->count();

        // Get the count of unread chats
        $chatsCount = Message::where('receiver_id', $userId)->where('status', 'unread')->count();

        return response()->json([
            'status' => 'success',
            'notificationsCount' => $notificationsCount,
            'chatsCount' => $chatsCount,
        ]);
    }
    public function updateField(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'field' => 'required|string',
            'value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = auth()->user();

        // Handle email-specific logic
        if ($request->field === 'email') {
            $newEmail = $request->value;

            // Step 1: Send OTP to existing email for confirmation
            $otp = rand(100000, 999999);
            $user->update(['otp' => $otp]);

            Mail::to($user->email)->send(new \App\Mail\OtpMail($otp));

            return response()->json([
                'message' => 'OTP sent to your current email for confirmation. Please verify to proceed.'
            ]);
        }

        // General field update
        $user->update([$request->field => $request->value]);

        return response()->json(['message' => 'Profile updated successfully.']);
    }

    public function verifyAndUpdateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|integer',
            'new_email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = auth()->user();

        if ($user->otp !== $request->otp) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        // Update to new email
        $user->update([
            'email' => $request->new_email,
            'email_verified_at' => null,
            'otp' => null, // Clear OTP
        ]);

        // Send verification email to the new email
        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Email updated successfully. Verification sent to new email.']);
    }

    public function updateProfileImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg'
        ]);

        try {
            $user = auth()->user();
            
            if ($request->hasFile('image')) {
                $photo = $request->file('image');
                $filename = time() . '.' . $photo->getClientOriginalExtension();

                // Delete old photo if exists
                if ($user->photo && file_exists(public_path('uploads/users/' . $user->photo))) {
                    unlink(public_path('uploads/users/' . $user->photo));
                }

                // Save new photo to public/uploads/users directory
                $photo->move(public_path('uploads/users'), $filename);

                // Update user record with new filename
                $user->photo = $filename;
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Profile image updated successfully',
                    'image_url' => $filename
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No image file received'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
