<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use carbon\Carbon;
use Session;
use Auth;
use Image;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware("auth");
        $this->middleware("superadmin");
    }
    public function index(Request $request){

        $query = User::orderBy('id', 'desc');

        // Check if a search query is provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Get paginated users
        $allUser = $query->paginate(10);
        // Get the counts of users for the current week, current month, and total users
        $currentWeekUsers = User::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $currentMonthUsers = User::whereMonth('created_at', Carbon::now()->month)->count();
        $totalUsers = User::count();

        // Get the counts of posts (total posts and pending posts)
        $totalPosts = Post::count();
        $pendingPosts = Post::where('approval', 0)->with(['user','roomImages'])->get();
 

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $currentMonthUsers = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        // Get the first and last day of the current month
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
        // Pass the data to the view
         
        return view('admin.user.all-user',compact('allUser','currentWeekUsers',
            'currentMonthUsers',
            'totalUsers',
            'totalPosts',
            'pendingPosts',
            'dates',
            'userCounts',
            'currentMonthName'));
    }
    public function add(){
        return view('admin.user.add-user');
    }
    public function edit($id){
        $user = User::find($id); 
          // Get the counts of users for the current week, current month, and total users
        $currentWeekUsers = User::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $currentMonthUsers = User::whereMonth('created_at', Carbon::now()->month)->count();
        $totalUsers = User::count();

        // Get the counts of posts (total posts and pending posts)
        $totalPosts = Post::count();
        $pendingPosts = Post::where('approval', 0)->with(['user','roomImages'])->get();
 

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $currentMonthUsers = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        // Get the first and last day of the current month
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
        return view('admin.user.edit-user',compact('user','currentWeekUsers',
            'currentMonthUsers',
            'totalUsers',
            'totalPosts',
            'pendingPosts',
            'dates',
            'userCounts',
            'currentMonthName'));
    }
    public function view($id){
        $data=User::where('id',$id)->firstOrFail();
           // Get the counts of users for the current week, current month, and total users
        $currentWeekUsers = User::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $currentMonthUsers = User::whereMonth('created_at', Carbon::now()->month)->count();
        $totalUsers = User::count();

        // Get the counts of posts (total posts and pending posts)
        $totalPosts = Post::count();
        $pendingPosts = Post::where('approval', 0)->with(['user','roomImages'])->get();
 

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $currentMonthUsers = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        // Get the first and last day of the current month
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
        return view('admin.user.view-user',compact('data','currentWeekUsers',
            'currentMonthUsers',
            'totalUsers',
            'totalPosts',
            'pendingPosts',
            'dates',
            'userCounts',
            'currentMonthName'));
    }
    public function insert(Request $request){

        $this->validate($request,[
            'name'=> 'required',
            'email'=> 'required|email|unique:users|max:50',
            'username'=> 'required',
            'password'=> 'required',
            'confirmPassword'=> 'required|same:password',
            'role'=> 'required',
        ],[
            'name.required'=> 'Please enter user name',
            'email.required'=> 'Please enter user email',
            'username.required'=> 'Please enter user username',
            'password.required'=> 'Please enter user password',
            'confirmPassword.required'=> 'Please enter user confirm password',
            'role.required'=> 'Please enter user role',
        ]);

        $insert=User::insertGetId([
            'name'=>$request['name'],
            'phone'=>$request['phone'],
            'email'=>$request['email'],
            'username'=>$request['username'],
            'password'=>Hash::make($request['password']),
            'role'=>$request['role'],
            'created_at'=>Carbon::now()->toDateTimeString(),
        ]);

        if($request->hasFile('pic')){
            $image=$request->file('pic');
            $imageName=$insert.'-'.time().'.'.$image->getClientOriginalExtension();
            'Image'::make($image)->save('uploaded_images/users/'.$imageName);

            User::where('id', $insert)->update([
                'photo'=> $imageName,
            ]);
        }

        if($insert){
            Session::flash('success','Successfully registered User Information');
            return redirect('dashboard/user');
        }
        else{
            Session::flash('error','Opps ! Someting Wrong. Please try again');
            return redirect('dashboard/user/add');
        }

    }
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('dashboard.user')->with('error', 'User not found');
        }

        // Validate input fields
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:15',
            'role' => 'required|in:1,2',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Validate photo
        ]);

        // Update user details
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role = $request->role;

        // Handle photo upload if provided
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = time() . '.' . $photo->getClientOriginalExtension();

            // Save photo to public/uploads/users directory
            $photo->move(public_path('uploads/users'), $filename);

            // Delete old photo if exists
            if ($user->photo && file_exists(public_path('uploads/users/' . $user->photo))) {
                unlink(public_path('uploads/users/' . $user->photo));
            }

            // Save the new filename to the database
            $user->photo = $filename;
        }

        $user->save();
        Session::flash('success','User information updated successfully!');
        return redirect('dashboard/user');
        // return redirect()->route('admin.user.all-user')->with('success', 'User information updated successfully!');
    }

    public function getUserById($user_id) {
        // Find the user by the given user_id
        $user = User::find($user_id);

        // Check if the user exists
        if ($user) {
            return response()->json([
                'status' => 'success',
                'user' => $user
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ]);
        }
    }

}
