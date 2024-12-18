<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function index()
    {
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
        return view('admin.dashboard.index', compact(
            'currentWeekUsers',
            'currentMonthUsers',
            'totalUsers',
            'totalPosts',
            'pendingPosts',
            'dates',
            'userCounts',
            'currentMonthName'
        ));
    }
}
