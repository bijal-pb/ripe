<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Like;
use App\Models\VideoView;
use App\Models\Comment;
use App\Models\ProcedureStep;
use Auth;

class Video extends Model
{
    use HasFactory,SoftDeletes;

    protected $appends = ['is_liked','is_favourite','total_likes','total_favourite','total_views'];
  
    public $fillable = ['food_category_id','country_id','title','preparation_time','serves','difficulty','videos','ingredients','is_published','is_liked'];

    public function upload_by()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class,'video_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class,'video_id')->where('parent_id',null)->with('replies');
    }
    public function steps()
    {
        return $this->hasMany(ProcedureStep::class,'video_id');
    }
    public function chef_details()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function getIsLikedAttribute()
    {
        $like = Like::where('video_id',$this->id)->where('user_id',Auth::id())->first();
        if(isset($like)){
        return 1;
        }else {
        return 0;
        }

    }

    public function getVideosAttribute($value)
    {
       return  str_replace(' ', '+', $value);
    }
    
    public function getIsFavouriteAttribute()
    {
        $favourite = AddFavourite::where('video_id',$this->id)->where('user_id',Auth::id())->first();
        if(isset($favourite)){
        return 1;
        }else {
        return 0;
        }

    }
    public function getTotalLikesAttribute()
    {
        return $this->hasMany(Like::class,'video_id')->count();
    }
    public function getTotalViewsAttribute()
    {
        return $this->hasMany(VideoView::class,'video_id')->count();
    }

    public function getTotalFavouriteAttribute()
    {
        return $this->hasMany(AddFavourite::class,'video_id')->count();
    }
}
