<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Mail;

class NotificationController extends Controller
{
    public function app_notification()
    {
        return view('admin.notification.send');
    }

    public function send_notification(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'message' => 'required'
        ]);
        if($request->device_type == 'all')
        {
            $users = User::get();
        }
        else
        {
            $users = User::where('device_type',$request->device_type)->get();
        }
        foreach($users as $user)
        {
           sendPushNotification($user->device_token,$request->title,$request->message,1,$user->id);
        }
        return response()->json(['status'=>'success']);
    }

    public function contact(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['status'=>'error', 'error'=> $validator->errors()->first()]);
        }
        $data = [
            'username' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'mess' => $request->message
        ];
        $email = env('CONTACTUS_EMAIL');
        $name = $request->name;
        Mail::send('mail.contact', $data, function ($message) use ($email,$name) {
            $message->to($email, $name)->subject('Contact Us');
        });
        return response()->json(['status'=>'success']);
    }
}
