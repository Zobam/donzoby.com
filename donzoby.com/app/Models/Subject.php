<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        "name", "slug", "description", "long_description",
    ];

    /**
     * get course
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * get posts
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
