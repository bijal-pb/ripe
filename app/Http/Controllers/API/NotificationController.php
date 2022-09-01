<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use App\Traits\ApiTrait;
use Auth;
use Mail;

class NotificationController extends Controller
{
    use ApiTrait;
     /**
    * @OA\Get(
     *     path="/api/notifications",
     *     tags={"Notifications"},
     *     summary="notifications",
     *     security={{"bearer_token":{}}},  
     *     operationId="notifications list",
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
   public function notifications()
   {
    try{
       $notification = Notification::where('user_id',Auth::id())->orderBy('id','desc')->get();
       $unread_count = Notification::where('user_id',Auth::id())
                                   ->where('status',1)->count();
                                   
         $data = [
          'notification' => $notification,
          'unread' => $unread_count
        ];
       return $this->response($unread_count,'Notifications List');	
    }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
    }
   }

   /**
   * @OA\Get(
     *     path="/api/read/notifications",
     *     tags={"Notifications"},
     *     summary="read notification",
     *     security={{"bearer_token":{}}},  
     *     operationId="read notification",
   *     
   *     @OA\Response(
   *         response=200,
   *         description="Success",
   *         @OA\MediaType(
   *             mediaType="application/json",
   *         )
   *    ),
   *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *    @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *    @OA\Response(
     *         response=404,
     *         description="not found"
     *     ),
     *    @OA\Response(
     *         response=422,
     *         description="Unprocessable entity"
     *     ),
     * )
   **/
   public function read_notifications()
   {
    try{
        $notifications = Notification::where('user_id',Auth::id())
                       ->where('status',1)
                       ->get();
        foreach($notifications as $noti)
        {
           $notification = Notification::find($noti->id);
           $notification->status = 2;
           $notification->save();
        }
        return $this->response('','Read status set successfully!');
    }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
    }
   }
}
