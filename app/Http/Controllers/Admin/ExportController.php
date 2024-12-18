<?php 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\UserStatisticsExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportUserStatistics()
    {
        $currentWeekUsers = DB::table('users')
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $currentMonthUsers = DB::table('users')
            ->whereMonth('created_at', now()->month)
            ->count();

        $totalUsers = DB::table('users')->count();
        $totalPosts = DB::table('posts')->count();

        $data = [
            ['Weekly Users', $currentWeekUsers],
            ['Monthly Users', $currentMonthUsers],
            ['Total Users', $totalUsers],
            ['Total Posts', $totalPosts],
        ];

        return Excel::download(new UserStatisticsExport($data), 'user_statistics.xlsx');
    }
}
