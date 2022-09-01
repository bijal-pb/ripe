<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiTrait;
use App\Models\User;
use App\Models\ProcedureStep;
use App\Models\Video;
use App\Models\Comment;
use App\Models\Like;
use App\Models\VideoView;
use App\Models\FoodMethod;
use App\Models\VideoFoodMethod;
use App\Models\Subscriber;
use App\Models\Setting;
use Carbon\Carbon;
use Log;
use Mail;
use Auth;
use DB;

class VideoController extends Controller
{
    use ApiTrait;
     /**
     *  @OA\Post(
     *     path="/api/video/add",
     *     tags={"Video"},
     *     summary="Add / Edit Video",
     *     security={{"bearer_token":{}}},
     *     operationId="add/video",
     * 
     *     @OA\Parameter(
     *         name="video_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="food_category_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *       @OA\Parameter(
     *         name="country_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="preparation_time",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="serves",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="difficulty",
     *         description="1 - easy | 2 - medium | 3 - hard",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="videos",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="ingredients",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="thumbnail",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
      *      @OA\Parameter(
     *         name="steps",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *       @OA\Parameter(
     *         name="food_methods",
     *         in="query",
     *         description="enter food methods ID with comma like 1,2",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
    **/
    public function add_video(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'video_id' => 'nullable|exists:videos,id',
            'food_category_id' => 'nullable|exists:food_categories,id',
            'country_id' => 'nullable|exists:countries,id',
            'preparation_time' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,400);
        }
        DB::beginTransaction();
        try{
           
            if($request->video_id != null){
                $video = Video::find($request->video_id);
                if($video->user_id != Auth::id()){
                    return $this->response([], "You have not permission for edit video", false,400);
                }

                $message = "Video updated successfully!";
            }
            else{
                $user = User::find(Auth::id());
                if($user->type != 1){
                    return $this->response([], "You have not permission for add video", false,400);
                }
                $video = new Video;
                $message = "Video added successfully!";
            }
           

            $video->user_id = Auth::id();
            $video->food_category_id = $request->food_category_id;
            $video->country_id = $request->country_id;
            $video->title = $request->title;
            $video->preparation_time = $request->preparation_time;
            $video->serves = $request->serves;
            $video->difficulty = $request->difficulty;
            $video->videos = $request->videos;
            $video->ingredients = $request->ingredients;
            $video->is_published = 0;
            $video->thumbnail = $request->thumbnail;
            $video->save();

            
            if($request->video_id != null){
                $f_method = VideoFoodMethod::where('video_id',$request->video_id)->pluck('food_mehod_id')->toArray();
                $food_methods = explode(",",$request->food_methods);
                $diffs = array_merge(array_diff($f_method, $food_methods),array_diff($food_methods,$f_method));
                foreach($diffs as  $d){
                    $fr = VideoFoodMethod::where('food_mehod_id',$d)->where('video_id',$request->video_id)->first(); 
                    if($fr){
                        $fr->delete();
                    }else{
                        $food_method = new VideoFoodMethod;
                        $food_method->video_id = $request->video_id;
                        $food_method->food_mehod_id = $d;
                        $food_method->save();
                    }
                }
            }else{
                $food_methods = explode(",",$request->food_methods);
                foreach($food_methods as $fm){
                    $food_m = new VideoFoodMethod;
                    $food_m->video_id = $video->id;
                    $food_m->food_mehod_id = $fm;
                    $food_m->save();
                }
            }
            //procedure steps
            if($request->video_id != null){
                $p_step1 = ProcedureStep::where('video_id',$request->video_id)->pluck('step')->toArray();
                $Procedure_Step = json_decode($request->steps);
                $diffs = array_merge(array_diff($Procedure_Step, $p_step1),array_diff($p_step1,$Procedure_Step));
                foreach($diffs as  $d){
                    $ps = ProcedureStep::where('step',$d)->where('video_id',$request->video_id)->first(); 
                    if($ps){
                        $ps->delete();
                    }else{
                        $pr_steps = new ProcedureStep;
                        $pr_steps->video_id = $request->video_id;
                        $pr_steps->step = $d;
                        $pr_steps->save();
                    }
                
                }  
            }else {
                if(is_array($request->steps)){
                    $p_steps = $request->steps;
                    foreach($p_steps as $stp){
                        $pro_steps = new ProcedureStep;
                        $pro_steps->video_id = $video->id;
                        $pro_steps->step = $stp;
                        $pro_steps->save();
                    }
                    if(count($p_steps) < 4){
                        $video->is_published = 0;
                        $video->save();


                        $data = [
                            'video_title' => $video->title,
                            'chef_name' => Auth::user()->name,
                        ];
                        $setting = Setting::first();
                        $admin_email = $setting->email;
                        $chef_email = Auth::user()->email;
                        try{
                            Mail::send('mail.not_publish_admin', $data, function ($message) use ($admin_email) {
                                $message->to($admin_email, 'RIPE')->subject('Not publish chef video!');
                            });
                            Mail::send('mail.not_publish_chef', $data, function ($message) use ($chef_email) {
                                $message->to($chef_email, 'RIPE')->subject('Not publish video!');
                            });
                        }catch(Exception $e){
                            Log::info('email-not-send:'.$e);
                        }
                        
                    }else{
                        $video->is_published = 1;
                        $video->save();
                        $subscribers = Subscriber::where('chef_id',Auth::id())->pluck('learner_id')->toArray();
                        $user_tokens = User::whereIn('id',$subscribers)->where('is_notification',1)->pluck('device_token')->toArray();
                        sendPushNotification($user_tokens,'New video in ripe.',Auth::user()->name.' has uploaded new video in ripe.',1,null,$video->id);
                    }
                }
            }
        
            DB::commit();
            return $this->response($video,$message);
        }catch(Exception $e){
            DB::rollback();
            return $this->response([], $e->getMessage(), false,404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/videos/list",
     *     tags={"Video"},
     *     security={{"bearer_token":{}}},  
     *     summary="Perticular chef with title search",
     *     security={{"bearer_token":{}}},
     *     operationId="fatch videos",
     *     
	 *     @OA\Parameter(
     *         name="search",
     *         description="search by title",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="food_category_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="country_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
    **/
    public function videos_list(Request $request){

        $validator = Validator::make($request->all(),[
            'video_id' => 'nullable|exists:videos,id',
            'food_category_id' => 'nullable|exists:food_categories,id',
            'country_id' => 'nullable|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,400);
        }
		try{
                $video = Video::where('user_id',Auth::id())->where('is_published',1)->with(['upload_by'])->orderBy('id','desc');
                if($request->search != null){
                    $video =  $video->where('title','LIKE','%'.$request->search.'%');
                }
                if($request->food_category_id != null){
                    $video =  $video->where('food_category_id',$request->food_category_id);
                }
                if($request->country_id != null){
                    $video =  $video->where('country_id',$request->country_id);
                }
                $video = $video->paginate(20);
				return $this->response($video,'Videos List');	
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }

     /**
     * @OA\Get(
     *     path="/api/video/detail",
     *     tags={"Video"},
     *     security={{"bearer_token":{}}},  
     *     summary="Perticular video details",
     *     security={{"bearer_token":{}}},
     *     operationId="video detail",
     *     
     *    @OA\Parameter(
     *         name="video_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
    **/
    public function video_detail(Request $request){

        $validator = Validator::make($request->all(),[
            'video_id' => 'nullable|exists:videos,id',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,400);
        }
		try{
                $video = Video::where('id',$request->video_id)->where('is_published',1)->with(['chef_details','likes','comments','steps'])->orderBy('id','desc')->get();
				return $this->response($video,'Video details');	
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }

   /**
     *  @OA\Post(
     *     path="/api/video/like",
     *     tags={"Video"},
     *     summary="Like Dislike Video",
     *     security={{"bearer_token":{}}},
     *     operationId="like/video",
     * 
     *     @OA\Parameter(
     *         name="video_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *    ),
     *    @OA\Parameter(
	 *         name="status",
	 *         in="query",
	 *         required=true,
	 * 		   description="1 - like  | 2 - dislike",
	 *         @OA\Schema(
	 *             type="integer"
	 *         )
	 *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
    **/
    public function video_like(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'video_id' => 'required',
            'status' => 'required|in:1,2'
        ]);
        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,400);
        }
        try {
            if($request->status == 1)
            {
                $video_like = Like::where('video_id',$request->video_id)->where('user_id',Auth::id())->first();
                if($video_like)
                {
                    return $this->response(null, 'Video is alredy liked.');
                } else {
                    $video_like = new Like;
                    $video_like->video_id = $request->video_id;
                    $video_like->user_id = Auth::id();
                    $video_like->save();
                    $video = Video::find($request->video_id);
                    $user = User::find($video->user_id);
                    sendPushNotification($user->device_token,'Video Like',Auth::user()->name.' has liked your video ',1,$user->id,$video->id);
                    return $this->response(null, 'Video liked!');
                }
            }
            if($request->status == 2)
            {
                $video_like = Like::where('video_id',$request->video_id)->where('user_id',Auth::id())->first();
                if($video_like)
                {
                    $video_like->delete();
                    $video = Video::find($request->video_id);
                    $user = User::find($video->user_id);
                    sendPushNotification($user->device_token,'Video Dislike',Auth::user()->name.' has disliked your video ',1,$user->id,$video->id);
                    return $this->response(null, 'Video disliked');    
                } else {
                    return $this->response(null, 'Video is not exists!'); 
                }
            }
        } catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }

    /**
     *  @OA\Post(
     *     path="/api/video/view",
     *     tags={"Video"},
     *     summary="Video view",
     *     security={{"bearer_token":{}}},
     *     operationId="video-view",
     * 
     *     @OA\Parameter(
     *         name="video_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *    ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
    **/
    public function video_view(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'video_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,400);
        }
        try {
                $video_view = VideoView::where('video_id',$request->video_id)->where('user_id',Auth::id())->first();
                if($video_view)
                {
                    return $this->response(null, 'Video is already view.');
                } else {
                    $video_view = new VideoView;
                    $video_view->video_id = $request->video_id;
                    $video_view->user_id = Auth::id();
                    $video_view->save();
                    return $this->response(null, 'Video viewed!');
                }
        } catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }

    /**
     *  @OA\Get(
     *     path="/api/home",
     *     tags={"Home"},
     *     security={{"bearer_token":{}}},  
     *     summary="Home",
     *     operationId="home",
     * 
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
    **/
    public function home(Request $request)
    {
        try{
            //$chefs = User::whereDate('created_at', Carbon::today())->where('type',2)->get();
            $videos = Video::leftJoin('likes', function($ljoin){
                            $ljoin->whereNull('likes.deleted_at')->on('likes.video_id','videos.id');
                        })
                        ->leftJoin('video_views', function($lj){
                            $lj->whereNull('video_views.deleted_at')->on('video_views.video_id','videos.id');
                        })
                        ->select('videos.user_id as user_id',DB::raw("COUNT(likes.id) as vid_likes"), DB::raw("COUNT(video_views.id) as vid_views"))
                        ->groupBy('likes.video_id')
                        ->groupBy('video_views.video_id')
                        ->orderBy('vid_likes','desc')
                        ->orderBy('vid_views','desc')
                        ->pluck('videos.user_id')->toArray();
            $chef_ids = array_unique($videos);
            
            // return $this->response($videos, 'Home!');
            // $chef_ids = Video::select(\DB::raw("COUNT(*) as count"), 'user_id')
            //         ->groupBy('user_id')
            //         ->orderBy('count', 'desc')
            //         ->get();
            $chefs = User::whereIn('id',$chef_ids)->where('status',1)->limit(25)->get();
            // foreach($chef_ids as $c){
            //     $chef = User::find($c->user_id);
            //     array_push($chefs, $chef);
            // }
            $f_method = FoodMethod::select('id','name')->get();
            $data = [];
            $foodMethods = [];
            $data['Trending Chefs'] = $chefs;
            foreach ($f_method as $fm){
                $foodVideos = '';
                $foodVids = [];
                $videoIds = VideoFoodMethod::where('food_mehod_id', $fm->id)->pluck('video_id')->toArray();;
                $videos = Video::whereRelation('upload_by', 'status',1)->whereIn('id',$videoIds)->where('is_published',1)->get();
                $foodVids[$fm->name] = $videos;
                if(count($videos) > 0){
                    array_push($foodMethods,$foodVids);
                }
            }
            $data['food_method_videos'] = $foodMethods;
            return $this->response($data, 'Home!'); 
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false);
        }
    }


    /**
     *  @OA\Post(
     *     path="/api/video/comment",
     *     tags={"Video"},
     *     summary="Add Comment",
     *     security={{"bearer_token":{}}},
     *     operationId="comment/video",
     * 
     *    @OA\Parameter(
     *         name="video_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *    ),
     * 
     *    @OA\Parameter(
     *         name="comment_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *    ),
     * 
     *     @OA\Parameter(
     *         name="comment",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
    **/
    public function video_comment(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'video_id' => 'required',
            'comment_id' => 'nullable|exists:comments,id',
            'comment' => 'required|max:255'
        ]);

        if($validator->fails())
        {
            return $this->response([], $validator->errors()->first(), false,400);
        }
        try{
            $comment = new Comment;
            $comment->user_id = Auth::id();
            $comment->video_id = $request->video_id;
            if(isset($request->comment_id) && $request->comment_id != null){
                $comment->parent_id = $request->comment_id;
            }
            $comment->comment = $request->comment;
            $comment->save();
            $video = Video::find($request->video_id);
            $user = User::find($video->user_id);
            if(isset($request->comment_id) && $request->comment_id != null){
                $com = Comment::find($request->comment_id);
                $com_user = User::find($com->user_id);
                sendPushNotification($com_user->device_token,'Video Comment',Auth::user()->name.' has replied on your comment',1,$com_user->id,$video->id,$comment->id,'comment');
            }else{
                sendPushNotification($user->device_token,'Video Comment',Auth::user()->name.' has commented on your video ',1,$user->id,$video->id,$comment->id,'comment');
            }
            return $this->response($comment,'Comment is added successfully.');
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/video/commnet/list",
     *     tags={"Video"},
     *     security={{"bearer_token":{}}},  
     *     summary="Perticular video comments",
     *     security={{"bearer_token":{}}},
     *     operationId="video comment",
     *     
     *    @OA\Parameter(
     *         name="video_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
    **/
    public function get_commnet(Request $request){

        $validator = Validator::make($request->all(),[
            'video_id' => 'nullable|exists:videos,id',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,400);
        }
		try{
                $comment = Comment::where('video_id',$request->video_id)->with(['comment_by','replies'])->where('parent_id',null)->orderBy('id','desc')->get();
				return $this->response($comment,'Video comments list');	
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }

     /**
     * @OA\Get(
     *     path="/api/videos/get",
     *     tags={"Video"},
     *     security={{"bearer_token":{}}},  
     *     summary="all videos get",
     *     security={{"bearer_token":{}}},
     *     operationId="all videos",
     *     
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="country_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *    @OA\Parameter(
     *         name="food_category_id",
     *         in="query",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
    **/
    public function videos_get(Request $request){

        $validator = Validator::make($request->all(),[
            'user_id' => 'nullable|exists:users,id',
            'food_category_id' => 'nullable|exists:food_categories,id',
            'country_id' => 'nullable|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,400);
        }
		try{
             $video = Video::where('is_published',1)->with(['upload_by'])->orderBy('id','desc');
             
             if($request->user_id != null){
                    $video =  $video->where('user_id',$request->user_id);
               }
             if($request->country_id != null){
                $video =  $video->where('country_id',$request->country_id);
             }
             if($request->food_category_id != null){
                $video =  $video->where('food_category_id',$request->food_category_id);
             }
              $video = $video->paginate(20);
			return $this->response($video,'Videos Get');	
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/videos/list/learner",
     *     tags={"Video"},
     *     security={{"bearer_token":{}}},  
     *     summary="Perticular learner with title search",
     *     security={{"bearer_token":{}}},
     *     operationId="learner search",
     *     
	 *     @OA\Parameter(
     *         name="search",
     *         description="search by title",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
    **/
    public function videos_list_learner(Request $request){

        $validator = Validator::make($request->all(),[
            
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,400);
        }
		try{
                $video = Video::whereRelation('upload_by', 'status',1)->where('is_published',1)->with(['upload_by'])->orderBy('id','desc');
                if($request->search != null){
                    $video =  $video->where('title','LIKE','%'.$request->search.'%');
                }
                $video = $video->paginate(20);
				return $this->response($video,'Learner videos List');	
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }


}
