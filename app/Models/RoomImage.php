<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'image_url'
    ];

    // Relationship with Post model
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
