<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VideoFoodMethod extends Model
{
    use HasFactory,SoftDeletes;

    public function food_method()
    {
        return $this->hasOne(FoodMethod::class,'id','food_mehod_id');
    }
    public function videos()
    {
        return $this->hasOne(Video::class,'id','video_id')->where('is_published',1);
    }
}
