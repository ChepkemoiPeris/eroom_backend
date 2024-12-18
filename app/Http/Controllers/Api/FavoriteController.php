<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite; 
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function listFavorites()
    {
        $favorites = Favorite::where('user_id', auth()->id())
        ->with(['post.roomImages'])  
        ->get();


        return response()->json(['favorites' => $favorites]);
    }

    public function addFavorite(Request $request)
        {
            $request->validate([
                'post_id' => 'required|exists:posts,id',
            ]);

            $favorite = Favorite::firstOrCreate([
                'user_id' => auth()->id(),
                'post_id' => $request->post_id,
            ]);

            return response()->json(['message' => 'Post added to favorites', 'favorite' => $favorite]);
        }

    public function removeFavorite(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);

        Favorite::where('user_id', auth()->id())
            ->where('post_id', $request->post_id)
            ->delete();

        return response()->json(['message' => 'Post removed from favorites']);
    }

     public function listFavoritePost(Request $request)
    {
         $request->validate([
            'post_id' => 'required|exists:posts,id',
        ]);
        $favorites = Favorite::where('user_id', auth()->id())
            ->where('post_id',$request->post_id)  
            ->first();

        return response()->json(['favorites' => $favorites]);
    }

}