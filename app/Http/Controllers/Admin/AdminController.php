<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Video;
use App\Models\FoodCategory;


class AdminController extends Controller
{
    public function index()
    {
        
        $data = (object) [];
       // $data->total_users = User::count();
        $data->total_cook = User::where('type',1)->count();
        $data->total_learn = User::where('type',2)->count();
        $data->total_videos = Video::count();
        $data->total_companies = 20;
        $data->total_food_category = FoodCategory::count();

        $chart_data = User::select(\DB::raw("COUNT(*) as count"), \DB::raw("(DATE_FORMAT(created_at, '%d-%m-%Y')) as udate"))
                        ->groupBy('udate')
                        ->get();
        $cData = [];

        foreach($chart_data as $row) {
            $timestamp = null;
            // $date = "1-".$row->monthyear;
            $timestamp = strtotime(date($row->udate)) * 1000; 
            array_push($cData,[$timestamp, (int) $row->count]);
        }

        $data->chart_data = json_encode($cData);
        return view('admin.home')->with("data", $data);
    }
}
