<?php
 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\RoomImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Session;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail; 

class PostController extends Controller
{

    public function __construct(){
        $this->middleware("auth");
        $this->middleware("superadmin");
    }
 
    public function index(Request $request)
    {
        // Retrieve query parameters
        $search = $request->input('search');
        $status = $request->input('status');
        $availability = $request->input('availability');

        // Start building the query
        $query = Post::with('user')->orderBy('created_at', 'desc');

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Apply status filter
        if (!is_null($status)) {
            $query->where('approval', $status);
        }

        // Apply availability filter
        if (!empty($availability)) {
            $query->where('status', $availability);
        }

        // Execute the query
        $posts = $query->get();

          // Get the counts of users for the current week, current month, and total users
        $currentWeekUsers = User::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $currentMonthUsers = User::whereMonth('created_at', Carbon::now()->month)->count();
        $totalUsers = User::count();

        // Get the counts of posts (total posts and pending posts)
        $totalPosts = Post::count();
        $pendingPosts = Post::where('approval', 0)->with(['user','roomImages'])->limit(10)->get();        
         
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Generate an array of all dates in the current month
        $dates = [];
        $userCounts = [];
        $currentDate = $startOfMonth;

        while ($currentDate <= $endOfMonth) {
            $dates[] = $currentDate->format('d');
            
            // Example: Replace this with your query logic to count users per day
            $userCounts[] = User::whereDate('created_at', $currentDate)->count();
            
            $currentDate->addDay();
        }
        $currentMonthName = Carbon::now()->format('F'); 
        $posts = $query->paginate(10);
        return view('admin.posts.index', compact('posts', 'currentWeekUsers',
            'currentMonthUsers',
            'totalUsers',
            'totalPosts',
            'dates',
            'userCounts',
            'currentMonthName',
            'pendingPosts'));
    }

    // Show a single post
    public function show($id)
    {
        $data = Post::with('roomImages')->findOrFail($id);
          // Get the counts of users for the current week, current month, and total users
        $currentWeekUsers = User::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $currentMonthUsers = User::whereMonth('created_at', Carbon::now()->month)->count();
        $totalUsers = User::count();

        // Get the counts of posts (total posts and pending posts)
        $totalPosts = Post::count();
        $pendingPosts = Post::where('approval', 0)->with(['user','roomImages'])->limit(10)->get();        
         
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Generate an array of all dates in the current month
        $dates = [];
        $userCounts = [];
        $currentDate = $startOfMonth;

        while ($currentDate <= $endOfMonth) {
            $dates[] = $currentDate->format('d');
            
            // Example: Replace this with your query logic to count users per day
            $userCounts[] = User::whereDate('created_at', $currentDate)->count();
            
            $currentDate->addDay();
        }
        $currentMonthName = Carbon::now()->format('F'); 
 
        return view('admin.posts.view',compact('data','currentWeekUsers',
            'currentMonthUsers',
            'totalUsers',
            'totalPosts',
            'dates',
            'userCounts',
            'currentMonthName',
            'pendingPosts'));
        
    }

    // Create a new post
    public function store(Request $request)
    {
        // Validate the incoming data
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_type' => 'required|in:cottage,room',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'province' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'suburb' => 'required|string|max:100',
            'status' => 'required|in:available,not available',
            'business_number' => 'nullable|string|max:15',
            'images' => 'nullable|array',  // images should be an array of URLs
        ]);

        // Create the post
        $post = Post::create($validated);

        // Attach images if they are provided
        if ($request->has('images')) {
            foreach ($request->images as $image) {
                RoomImage::create([
                    'post_id' => $post->id,
                    'image_url' => $image
                ]);
            }
        }

        return response()->json(['message' => 'Post created successfully', 'post' => $post], 201);
    }

    // Update a post
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Validate the incoming data
        $validated = $request->validate([
            'room_type' => 'required|in:cottage,room',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'province' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'suburb' => 'required|string|max:100',
            'status' => 'required|in:available,not available',
            'business_number' => 'nullable|string|max:15',
            'approval' => 'nullable|in:0,1', // Approval status (0 - pending, 1 - approved)
            'images' => 'nullable|array',  // images should be an array of URLs
        ]);

        $post->update($validated);

        // Update images if provided
        if ($request->has('images')) {
            // Remove old images
            RoomImage::where('post_id', $post->id)->delete();
            
            foreach ($request->images as $image) {
                RoomImage::create([
                    'post_id' => $post->id,
                    'image_url' => $image
                ]);
            }
        }

        return response()->json(['message' => 'Post updated successfully', 'post' => $post]);
    }
    public function approve($id)
    {
        $post = Post::find($id);

         if ($post->approval == 1) {
            return redirect()->back()->with('error', 'This post is already approved.');
        }
        $post->approval = 1; // Mark as approved
        $post->save();

        Notification::create([
        'user_id' => $post->user_id,
        'type' => 'post_approved',
            'message' => json_encode([
                'post_id' => $post->id,
                'title' => $post->title,
                'content' => 'Your post has been approved.',
            ]),
        ]);
        //send an email to user
        \Mail::to($post->user->email)->send(new \App\Mail\PostStatusNotification($post, 1));

        return redirect()->back()->with('success', 'Post approved successfully');
    }

    public function decline(Request $request, $id)
    {
        $request->validate([
            'decline_reason' => 'required|string',
        ]);
    
        $post = Post::findOrFail($id);
        if ($post->approval == 2) {
            return redirect()->back()->with('error', 'This post has already been declined.');
        }
        $post->approval = 2; // Mark as declined
        $post->decline_reason = $request->decline_reason; // Save decline reason
        $post->save();

        Notification::create([
        'user_id' => $post->user_id,
        'type' => 'post_declined',
        'message' => json_encode([
            'post_id' => $post->id,
                'title' => $post->title,
                'content' => 'Your post has been declined. Reason: ' . $request->decline_reason,
            ]),
        ]);
        //send an email to user
        \Mail::to($post->user->email)->send(new \App\Mail\PostStatusNotification($post, 2, $request->decline_reason));

        return redirect()->back()->with('success', 'Post declined successfully with a reason.');
    }
    

    // Delete a post
    public function destroy()
    {
        
        $id=$_POST['modal_id'];
        $post = Post::findOrFail($id);
        $delete = $post->delete(); 

        if($delete){
            Session::flash('success','Post deleted successfully');
            return redirect('dashboard/posts');
        }
        else{
            Session::flash('error','Opps ! Someting Wrong. Please try again');
            return redirect('dashboard/posts');
        } 
    }
}
