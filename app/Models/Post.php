<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_type',
        'title',
        'description',
        'price',
        'province',
        'city',
        'suburb',
        'status',
        'approval',
        'business_number',
        'decline_reason'
    ];

    // Relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with the RoomImage model
    public function roomImages()
    {
        return $this->hasMany(RoomImage::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'post_id');
    }

}
