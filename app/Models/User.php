<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Follower;
use App\Models\Stock;
use App\Models\Video;
use App\Models\Subscriber;
use Auth;
use Illuminate\Support\Carbon;



class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $appends = ['is_subscribed','total_subscribers'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function retailer_design()
    {
        return $this->hasMany(RetailerDesign::class,'retailer_id')->with('design');
    }
    public function stock_detail()
    {
        return $this->hasMany(Stock::class,'user_id');
    }
    // public function company_detail()
    // {
    //     return $this->hasMany(Company::class,'user_id');
    // }
    public function order_detail()
    {
        return $this->hasMany(Order::class,'retailer_id');
    }
    public function wholeseller()
    {
        return $this->hasOne(User::class,'id','wholeseller_id');
    }
    public function all_uploaded_videos()
    {
        return $this->hasMany(Video::class,'user_id')->where('is_published',1);
    }
    public function getPhotoAttribute($value)
    {
        if ($value) {
            return asset('/user/' . $value);
        } else {
            return asset('/user/' . "logo.png");
        }
    }

    public function getIsSubscribedAttribute()
    {
        $subscribed = Subscriber::where('chef_id',$this->id)->where('learner_id',Auth::id())->first();
        if(isset($subscribed)){
            return 1;
        }else {
            return 0;
        }
    }

    public function getTotalSubscribersAttribute()
    {
        return $this->hasMany(Subscriber::class,'chef_id')->count();
    }
    public function getCreatedAtAttribute($value)
    {
        return (new Carbon($value))->format('Y-m-d');
    }
}
