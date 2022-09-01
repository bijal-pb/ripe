<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddFavourite extends Model
{
    use HasFactory,SoftDeletes;

    public $fillable = ['user_id','video_id'];

    public function videos()
    {
        return $this->hasOne(Video::class,'id','video_id')->where('is_published',1)->with(['upload_by']);
    }
}
