<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;
use App\Models\Video;
use App\Models\FoodMethod;
use App\Models\VideoFoodMethod;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Session;
use Hash;
use Mail;
use Exception;
use Log;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $u_videos = Video::select('videos.*','users.name as chef_name')
                        ->leftJoin('users','videos.user_id','users.id');
    
        if($request->search != null)
        {
            $u_videos = $u_videos->where('first_name','LIKE','%'.$request->search.'%')
                            ->orWhere('last_name','LIKE','%'.$request->search.'%')
                            ->orWhere('email','LIKE','%'.$request->search.'%');
        }
        
        if($request->sortby!= null && $request->sorttype)
        {
            $u_videos = $u_videos->orderBy($request->sortby,$request->sorttype);
        }else{
            $u_videos = $u_videos->orderBy('id','desc');
        }
        if($request->perPage != null){
            $u_videos = $u_videos->paginate($request->perPage);
        }else{
            $u_videos = $u_videos->paginate(10);
        }
        $videosChef = User::select('id','name')->get();
        if($request->ajax())
        {
            return response()->json( view('admin.video.video_data', compact('u_videos'))->render());
        }
        return view('admin.video.list' , compact(['u_videos','videosChef']));
    }

    public function videos(Request $request)
    {
        $columns = array( 
            0 =>'id',
            1 =>'title', 
            2 =>'videos',
            3 =>'chef_name',
            4 => 'food_category_name',
            5 => 'course',
            6 => 'country_id',
            7 => 'preparation_time',
            8 => 'serves',
            9 =>'is_published',
        );  
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $u_videos = Video::select('videos.*','food_categories.name as food_category_name','users.name as chef_name','countries.name as country_name', 'food_methods.name as course')
                        ->leftJoin('video_food_methods as fm', 'videos.id', '=', 'fm.video_id')
                        ->leftJoin('food_methods', 'fm.food_mehod_id', '=', 'food_methods.id')
                        ->leftJoin('food_categories', 'videos.food_category_id','=','food_categories.id')
                        ->leftJoin('users','videos.user_id','=','users.id')
                        ->leftJoin('countries','videos.country_id','=','countries.id');
        if($request->is_published != null){
            $u_videos = $u_videos->where('is_published',$request->is_published);
        }

        if($request->search['value'] != null){
            $u_videos = $u_videos->where('users.name','LIKE','%'.$request->search['value'].'%')
                                 ->orWhere('countries.name','LIKE','%'.$request->search['value'].'%')
                                 ->orWhere('food_categories.name','LIKE','%'.$request->search['value'].'%')
                                 ->orWhere('videos.title','LIKE','%'.$request->search['value'].'%');
        }
        if($request->length != '-1')
        {
            $u_videos = $u_videos->take($request->length);
        }else{
            $u_videos = $u_videos->take(Video::count());
        }
        $u_videos = $u_videos->skip($request->start)
                        ->orderBy($order,$dir)
                        ->get();
       
        $data = array();
        if(!empty($u_videos))
        {
            foreach ($u_videos as $user_videos)
            {
                $url = route('admin.video.get', ['user_id' => $user_videos->id]);
                $videoUrl = $user_videos->videos;
                $statusUrl = route('admin.video.status.change', ['user_id' => $user_videos->id]);
                $checked = $user_videos->is_published == 1 ? 'checked' : '';

                $nestedData['id'] = $user_videos->id;
                $nestedData['title'] = $user_videos->title;
                $nestedData['videos'] =   "<span class='vid-btn' style='cursor:pointer;' data-url='$videoUrl' data-toggle='modal' data-target='.example-modal-fullscreen'><img src ='$user_videos->thumbnail'  height='50' width='50'></span>";
                $nestedData['chef_name'] = $user_videos->chef_name;
                $nestedData['food_category_name'] = $user_videos->food_category_name;
                $nestedData['course'] = $user_videos->course;
                $nestedData['country_id'] = $user_videos->country_name;
                $nestedData['preparation_time'] = $user_videos->preparation_time;
                $nestedData['serves'] = $user_videos->serves;
               
                $nestedData['is_published'] = "<div class='custom-control custom-switch'>
                                            <input type='radio' class='custom-control-input active' data-url='$statusUrl' id='active$user_videos->id' name='active$user_videos->id' $checked>
                                            <label class='custom-control-label' for='active$user_videos->id'></label>
                                        </div>";
                $data[] = $nestedData;

            }
        }
        return response()->json([
            'draw' => $request->draw,
            'data' =>$data,
            'recordsTotal' => Video::count(),
            'recordsFiltered' => $request->search['value'] != null ? $u_videos->count() : Video::count(),
        ]);
    }
    public function getVideo(Request $request){
        $u_videos = Video::find($request->user_id);
        return response()->json(['data'=>$u_videos]);
    }
    public function changeStatus(Request $request){
        $u_videos = Video::find($request->user_id);
        if($u_videos->is_published == 1)
        {
            $u_videos->is_published = 0;
            $chef = User::find($u_videos->user_id);
            sendPushNotification($chef->device_token,'Video Unpublished.',$u_videos->title.' is Unpublished.',1,null,$u_videos->id);
        }else{
            $u_videos->is_published = 1;
            $chef = User::find($u_videos->user_id);
            $data = [
                'video_title' => $u_videos->title,
                'chef_name' => $chef->name,
            ];
            $email = $chef->email;
            try{
                Mail::send('mail.publish_video', $data, function ($message) use ($email) {
                    $message->to($email, 'RIPE')->subject('Publish video!');
                });
            }catch(Exception $e){
                Log::info('mail issue: '.$e);
            }
            sendPushNotification($chef->device_token,'Video Published.',$u_videos->title.' is published.',1,null,$u_videos->id);
        }
        $u_videos->save();
        return response()->json(['status'=>'success']);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'videos.*' => 'nullable|mimes:mp4,ogx,oga,ogv,ogg,webm,avi,mov',
		]);

		if($validator->fails())
		{
            return response()->json(['status'=>'error','message' => $validator->errors()->first()]);
        }
    
        if($request->user_id != null)
        {
            $u_videos = Video::find($request->user_id);
        }else{
            $u_videos = new Video;
        }
        $u_videos->videos = $request->videos;
        $u_videos->food_category_id = $request->food_category_id;
        $u_videos->country_id = $request->country_id;
        $u_videos->preparation_time = $request->preparation_time;
        $u_videos->serves = $request->serves;
        $u_videos->is_published = $request->is_published;
        $u_videos->save();
        return response()->json(['status'=>'success']);
    }
}
