<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password as PasswordRules;

class AuthController extends Controller
{
    // User Registration
    public function register(Request $request)
    {
        
        // Validate the input fields including username, password, and confirm password
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:15|unique:users',
            'username' => 'string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'confirmPassword' => 'required|same:password', // Confirm password validation
        ]);
    
        // Check if an avatar is uploaded, otherwise use a default one
        $photo = 'avatar.png';  // Default avatar file name
    
        if ($request->hasFile('photo')) {
            // Handle avatar upload
            $image = $request->file('photo');
            $photo = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/avatars'), $photo);
        }
    
        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'], 
            'password' => Hash::make($validated['password']),
            'role' => 2,  // Default role for user
            'photo' => $photo,  // Store the file name of the avatar
        ]);
        event(new Registered($user));
        // Generate the API token for the user
        $token = $user->createToken('auth_token')->plainTextToken;
    
        // Return response with token and user details
        return response()->json([ 
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone, 
            'role' => $user->role,
            'photo' => $user->photo,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'access_token' => $token       
    ]);
    }
    

    // User Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (is_null($user->email_verified_at)) {
            return response()->json([
                'error' => 'Your email is not verified. Please verify your email before logging in.',
            ], 403);
        }
        $device_id = 'chat-room-' . $user->id; 
        $user->device_id = $device_id;   
        $user->save();
        $token = $user->createToken('auth_token')->plainTextToken;
 
         return response()->json([ 
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone, 
            'role' => $user->role,
            'photo' => $user->photo,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'device_id' => $device_id,
            'access_token' => $token,
            'ably_key' => env('ABLY_KEY')      
         ]);
    }

    // User Logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    //reset password
    public function forgotPassword(Request $request)
    {
        // Validate the email field
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Send the password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent to your email.']);
        } else {
            return response()->json(['error' => 'Unable to send reset link. Please try again later.'], 500);
        }
    }

   
    public function validateToken(Request $request)
    {
        try {
            // Ensure the token is attached in the Authorization header
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['valid' => false, 'message' => 'Token not provided'], 401);
            }

            // Authenticate the token using Sanctum
            $user = Auth::guard('sanctum')->user();

            if ($user) {
                return response()->json(['valid' => true, 'user' => $user]);
            }

            return response()->json(['valid' => false, 'message' => 'Invalid token'], 401);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Error validating token',
                'error' => $e->getMessage(),
            ], 401);
        }
    }

      public function changePassword(Request $request)
    {
          $validated = $request->validateWithBag('updatePassword', [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', PasswordRules::defaults()],
            ]);
 

            // Update the password if everything is valid
            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);

        return response()->json(['success' => true, 'msg' => "Password changed successfully"]); 
    }
}
