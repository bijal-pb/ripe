<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Comment extends Model
{
    use HasFactory;


    public function comment_by()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class,'parent_id')->with('comment_by');
    }

}
