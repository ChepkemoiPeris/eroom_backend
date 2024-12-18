<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\RoomImage;
use Illuminate\Http\Request;

class RoomController extends Controller
{

    // Display Rooms
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0); 

        $rooms = Post::with(['user', 'roomImages'])
                    ->withCount('favorites')
                    ->where('approval', 1)
                    ->where('status', 'available')
                    ->offset($offset)
                    ->limit($limit)
                    ->get();

        $total = Post::where('approval', 1)
                    ->where('status', 'available')
                    ->count();

        return response()->json([
            'rooms' => $rooms,
            'total' => $total,
        ]);

    }
    public function getUserRooms(Request $request){
        $rooms = Post::with(['user','roomImages'])->withCount('favorites') ->where('user_id',$request->user()->id)->get();
        return response()->json(['rooms' => $rooms]);
    }
    // Add Room
    public function store(Request $request)
    {
        
        // Validate the incoming data
        $validated = $request->validate([
            'room_type' => 'required|string|in:Cottage,Room',
            'price' => 'required|numeric',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'suburb' => 'required|string',
            'images' => 'nullable|array',  // Validate that images are an array (optional)
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif' // Validate individual images
        ]);
    
        // Create the room post
        $room = Post::create(array_merge($validated, ['user_id' => $request->user()->id]));
    
        // Check if there are images to upload
        if ($request->has('images') && is_array($request->images)) {
            // Loop through each image
            foreach ($request->images as $image) {
                // Store the image and get its URL
                $imagePath = $image->store('room_images', 'public');  // Store in the 'room_images' directory
    
                // Create an entry in the room_images table
                RoomImage::create([
                    'post_id' => $room->id,
                    'image_url' => $imagePath,  // Store the relative path
                ]);
            }
        }
    
        // Return the response with the room and images
        return response()->json(['message' => 'Room added successfully', 'room' => $room], 201);
    }
    
    
 // Update Room
 // Update Room
public function update(Request $request, $id)
{
    
    // Find the room by ID
    $room = Post::find($id); 
    // If the room does not exist, return a 404 response
    if (!$room) {
        return response()->json(['message' => 'Room not found'], 404);
    }

    // Check if the authenticated user is the owner of the room
    if ($room->user_id !== $request->user()->id) {
        return response()->json(['message' => 'You do not have permission to update this room'], 403);
    }

    // Validate the incoming data
    $validated = $request->validate([
        'room_type' => 'required|string|in:Cottage,Room',
        'price' => 'required|numeric',
        'approval' => 'required|numeric',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'province' => 'required|string',
        'city' => 'required|string',
        'suburb' => 'required|string',       
         'status' => 'string',    
        'existingImages' => 'array',
        'newImages.*' => 'file|image|max:2048', 
    ]); 
    
    // Update the room post with the validated data
    $room->update($validated);

    // Check if there are new images to upload
    if ($request->has('images') && is_array($request->images)) {
        // Loop through each image and store it
        foreach ($request->images as $image) {
            // Store the image and get its URL
            $imagePath = $image->store('room_images', 'public');  // Store in the 'room_images' directory

            // Create an entry in the room_images table
            RoomImage::create([
                'post_id' => $room->id,
                'image_url' => $imagePath,  // Store the relative path
            ]);
        }
    }
    if (isset($_POST['removed_image_ids'])) {
        $removedImageIds = explode(',', $_POST['removed_image_ids']);
        
        foreach ($removedImageIds as $imageId) {
            // Delete the image from storage (or database)
            // Assuming you have a function to delete the image by ID
            $this->deleteImageById($imageId);
        }
    }
    // Return the response with the updated room and its images
    return response()->json(['message' => 'Room updated successfully', 'room' => $room], 200);
}

private function deleteImageById($imageId) {
    // Find the image entry in the database
    $image = RoomImage::find($imageId);
    
    if ($image) {
        // Delete the image file from storage
        $imagePath = public_path('storage/' . $image->image_url); // Assuming the 'storage' folder is symbolic linked
        
        if (file_exists($imagePath)) {
            unlink($imagePath);  // Delete the file from the filesystem
        }
        
        // Delete the image record from the database
        $image->delete();
    }
}

 
public function search(Request $request)
{
    $query = Post::with(['user', 'roomImages']);

    // Apply room_type if it's provided
    if ($request->has('room_type')) {
        $query->where('room_type', $request->room_type);
    }

    // Apply price range filter if both min_price and max_price are provided
    if ($request->has('min_price') && $request->min_price > 0) {
        $query->where('price', '>=', $request->min_price);
    }

    if ($request->has('max_price') && $request->max_price != 0 && $request->max_price != 'Infinity') {
        $query->where('price', '<=', $request->max_price);
    }

    // Apply province filter if provided
    if ($request->has('province') && $request->province != '') {
        $query->where('province', 'like', '%' . $request->province . '%');
    }

    // Apply city filter if provided
    if ($request->has('city') && $request->city != '') {
        $query->where('city', 'like', '%' . $request->city . '%');
    }

    // Apply suburb filter if provided
    if ($request->has('suburb') && $request->suburb != '') {
        $query->where('suburb', 'like', '%' . $request->suburb . '%');
    }

    // Execute the query and get the results
    $rooms = $query->where('approval',1)->get();

    // Return the filtered rooms
    return response()->json(['rooms' => $rooms]);
}

}
