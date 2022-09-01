<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiTrait;
use App\Models\AddFavourite;
use App\Models\Subscriber;
use Auth;

class FavouriteController extends Controller
{
    use ApiTrait;
     /**
     *  @OA\Post(
     *     path="/api/favourite/add",
     *     tags={"Favourite"},
     *     summary="Add Favourite",
     *     security={{"bearer_token":{}}},
     *     operationId="add/favourite",
     * 
     *     @OA\Parameter(
     *         name="video_id",
     *         required=true,
     *         in="query",
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
    public function add_favoirite(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'video_id' => 'required|exists:videos,id',
        ]);

        if($validator->fails())
        {
            return $this->response([], $validator->errors()->first(), false,400);
        }
        try{
            if($request->video_id != null)
			{
				$add_favourite = AddFavourite::where('video_id',$request->video_id)->where('user_id',Auth::id())->first();
				if($add_favourite)
				{
                    return $this->response('','Video is already add to favourite list.');
				} else {
                        $add_favourite = new AddFavourite;
                        $add_favourite->user_id = Auth::id();
                        $add_favourite->video_id = $request->video_id;
                        $add_favourite->save();
                }
            }  
            return $this->response($add_favourite,'video is added favourite list successfully.');
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }

     /**
     *  @OA\Post(
     *     path="/api/favourite/remove",
     *     tags={"Favourite"},
     *     summary="Remove video from favourite list",
     *     security={{"bearer_token":{}}},
     *     operationId="remove/favourite",
     * 
     *      @OA\Parameter(
     *         name="video_id",
     *         required=true,
     *         in="query",
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
	public function remove_favourite(Request $request)
	{
		$validator = Validator::make($request->all(),[
			'video_id' => 'required|exists:videos,id',
		]);

        if($validator->fails())
        {
            return $this->response([], $validator->errors()->first(), false,400);
        }
		try {
			$r_favourite = AddFavourite::where('video_id',$request->video_id)->where('user_id',Auth::id())->first();
			if($r_favourite)
			{
				if($r_favourite->delete())
				{
					return $this->response('','Video is removed from favourite list.');
				}
			}
            return $this->sendError('Enter valid video id!.',422);
		}catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
	}

     /**
     * @OA\Get(
     *     path="/api/favourite/list",
     *     tags={"Favourite"},
     *     security={{"bearer_token":{}}},  
     *     summary="chefs videos favourite list",
     *     security={{"bearer_token":{}}},
     *     operationId="list/favourite",
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
    public function favourite_list(Request $request){
		try{
                $s_list = Subscriber::where('learner_id',Auth::id())->with(['chef'])->orderBy('id', 'desc')->get();
                $f_list = AddFavourite::where('user_id',Auth::id())->with(['videos'])->orderBy('id','desc')->get();
				return $this->response(['subscribe_list' => $s_list,'favourite_list'=>$f_list],'Favourite video list.');	
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }

}
