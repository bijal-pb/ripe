<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FoodCategory extends Model
{
    use HasFactory,SoftDeletes;

    public $fillable = ['name','image'];

    public function getImageAttribute($value)
    {
        if ($value) {
            return asset('/food_category/' . $value);
        } else {
            return null;
        }
    }
}
