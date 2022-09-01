<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AppVersion;
use App\Models\UserOtp;
use App\Models\Subscriber;
use App\Models\FamiliarRacipies;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Auth;
use Hash;
use DB;
use Mail;


/**
 * @OA\Info(
 *      description="",
 *     version="1.0.0",
 *      title="Ripe",
 * )
 **/

/**
 *  @OA\SecurityScheme(
 *     securityScheme="bearer_token",
 *         type="http",
 *         scheme="bearer",
 *     ),
 **/
class UserController extends Controller
{

    use ApiTrait;

    /**
    *  @OA\Post(
    *     path="/api/register",
    *     tags={"User"},
    *     summary="Register",
    *     operationId="register",
    *
    *   @OA\Parameter(
    *         name="name",
    *         in="query",
    *         required=true,
    *         @OA\Schema(
    *             type="string"
    *         )
    *     ),   
    *     @OA\Parameter(
    *         name="email",
    *         in="query",
    *         required=true,
    *         @OA\Schema(
    *             type="string"
    *         )
    *     ),    
    *     @OA\Parameter(
    *         name="password",
    *         in="query",
    *         required=true,
    *         @OA\Schema(
    *             type="string"
    *         )
    *     ),
    *     @OA\Parameter(
    *         name="confirm_password",
    *         in="query",
    *         required=true,
    *         @OA\Schema(
    *             type="string"
    *         )
    *     ),
    *   @OA\Parameter(
    *         name="type",
    *         in="query",
    *         description="1-cook | 2-learn",
    *         required=true,
    *         @OA\Schema(
    *             type="integer"
    *         )
    *     ),   
    *   @OA\Parameter(
    *         name="description",
    *         in="query",
    *         @OA\Schema(
    *             type="string"
    *         )
    *     ),   
    *   @OA\Parameter(
    *         name="country_id",
    *         in="query",
    *         @OA\Schema(
    *             type="string"
    *         )
    *     ),   
    *   @OA\Parameter(
    *         name="familiar_recipes",
    *         in="query",
    *        description="enter familiar recipice ID with comma like 1,2",
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
    * )
    **/
   
   public function register(Request $request)
   {
       $validator = Validator::make($request->all(),[

           'name' => 'required|max:255',
           'email' => 'required|email|unique:users',
           'password' => 'required|min:8|required_with:confirm_password|same:confirm_password',
           'confirm_password' => 'min:8',
           'type' => 'required|in:1,2',
           'description' => 'required_if:type,1',
           'country_id' => 'required_if:type,1',
           'familiar_recipes' => 'required_if:type,1',

       ]);

       if ($validator->fails()) {
        return $this->response([], $validator->errors()->first(), false, 400);
       }
       DB::beginTransaction();
       try{
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->type = $request->type;
            $user->is_verified = 2;
            if($user->type == 1){
                $user->description = $request->description;
                $user->country_id = $request->country_id;
            }  
            $user->save();
            if($request->familiar_recipes != null)
            {
                $familiar_recipes = explode(",",$request->familiar_recipes);
                foreach($familiar_recipes as  $fr){
                    $f_recipes = new FamiliarRacipies;
                        $f_recipes->user_id = $user->id;
                        $f_recipes->familiar_id = $fr;
                        $f_recipes->save();
                    }
            }   
            if($user->type == 1) {
                $user->assignRole([4]);
            }else{
             $user->assignRole([5]);
            }
            // $otp = rand(100000,999999);
            //     $data = [
            //         'username' => $user->name,
            //         'otp' => $otp
            //     ];
            //     UserOtp::where('user_id',$user->id)->delete();
            //     $saveOtp = new UserOtp;
            //     $saveOtp->user_id = $user->id;
            //     $saveOtp->otp = $otp;
            //     $saveOtp->save();
            //     $email = $user->email;
            //     $name = $user->name;
            //     Mail::send('mail.otp', $data, function ($message) use ($email,$name) {
            //         $message->to($email, $name)->subject('Otp');
            //     });
            DB::commit();
            return $this->response('','Registered Successully!');
    }catch(Exception $e){
        DB::rollback();
        return $this->response([], $e->getMessage(), false,404);
    }
 }


  /**
    *  @OA\Post(
    *     path="/api/social/register",
    *     tags={"User"},
    *     summary="Social Register",
    *     operationId="social register",
    *     security={{"bearer_token":{}}},
    *
    *   @OA\Parameter(
    *         name="name",
    *         in="query",
    *        required=true,
    *         @OA\Schema(
    *             type="string"
    *         )
    *     ),   
    *   @OA\Parameter(
    *         name="type",
    *         in="query",
    *         description="1-cook | 2-learn",
    *         required=true,
    *         @OA\Schema(
    *             type="integer"
    *         )
    *     ),   
    *   @OA\Parameter(
    *         name="description",
    *         in="query",
    *         @OA\Schema(
    *             type="string"
    *         )
    *     ),   
    *   @OA\Parameter(
    *         name="country_id",
    *         in="query",
    *         @OA\Schema(
    *             type="string"
    *         )
    *     ),   
    *   @OA\Parameter(
    *         name="familiar_recipes",
    *         in="query",
    *        description="enter familiar recipice ID with comma like 1,2",
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
    * )
    **/
   
    public function social_register(Request $request)
    {
        $validator = Validator::make($request->all(),[
 
            'name' => 'required|max:255',
            'type' => 'required|in:1,2',
            'description' => 'required_if:type,1',
            'country_id' => 'required_if:type,1',
            'familiar_recipes' => 'required_if:type,1',
 
        ]);
 
        if ($validator->fails()) {
         return $this->response([], $validator->errors()->first(), false, 400);
        }
        DB::beginTransaction();
        try{
            $user = User::find(Auth::id());
            if($user){
                $user->name = $request->name;
                $user->type = $request->type;
                if($user->type == 1){
                    $user->description = $request->description;
                    $user->country_id = $request->country_id;
                }  
             $user->save();
             if($request->familiar_recipes != null)
             {
                 $familiar_recipes = explode(",",$request->familiar_recipes);
                 foreach($familiar_recipes as  $fr){
                     $f_recipes = new FamiliarRacipies;
                         $f_recipes->user_id = $user->id;
                         $f_recipes->familiar_id = $fr;
                         $f_recipes->save();
                    }
                 } 
            }
             if($user->type == 1) {
                 $user->assignRole([4]);
             }else{
              $user->assignRole([5]);
             }
             DB::commit();
             return $this->response($user,'Updated Successully!');
        
     }catch(Exception $e){
         DB::rollback();
         return $this->response([], $e->getMessage(), false,404);
     }
  }

    /**
     *  @OA\Post(
     *     path="/api/login",
     *     tags={"Login"},
     *     summary="Login",
     *     operationId="login",
     * 
    *     @OA\Parameter(
   *         name="email",
   *         in="query",
   *         @OA\Schema(
   *             type="string"
   *         )
   *     ),      
   *     @OA\Parameter(
   *         name="password",
   *         in="query",
   *         @OA\Schema(
   *             type="string"
   *         )
   *     ), 
   *     @OA\Parameter(
   *         name="social_type",
   *         in="query",
   *         description="google | mac | facebook",   
   *         @OA\Schema(
   *             type="string"
   *         )
   *     ), 
   *     @OA\Parameter(
   *         name="social_id",
   *         in="query",
   *         @OA\Schema(
   *             type="string"
   *         )
   *     ),  
   * 
     *     @OA\Parameter(
     *         name="device_type",
     *         in="query",
     *         description="android | ios",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="device_token",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ), 
     *     @OA\Parameter(
     *         name="app_version",
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
     * )
     **/

    public function login(Request $request)
    {
        if(!isset($request->app_version)){
            return $this->response([], "Your App version is outdated, Please Update from store!", false, 400);
        }
        $validator = Validator::make($request->all(),[
            'social_type' => 'nullable|in:google,mac,facebook',
            'email' => 'nullable|required_if:social_type,null,google,facebook',
            'password' => 'required_if:social_type,null',
            'social_id' => 'required_if:social_type,google,mac,facebook',
        ],
         [
             'email.required_if' => 'Email is required',
             'password.required_if' => 'Password is required, if not login with social.',
             'social_id.required_if' => 'Social id is required.'
         ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false, 400);
        }

        if($request->social_type == null)
        {
            $credentials = request(['email','password']);
            if(!Auth::attempt($credentials))
            {
                return $this->response([], 'Please enter valid email or password!', false,401); 
            }
            $user = User::where('email', $request->email)->first();
            $user->device_type = $request->device_type;
            $user->device_token = $request->device_token;
            $user->save();
            $user->tokens()->delete();
            if($user->status == 2){
                return $this->response([], 'Your account is blocked, Please contact administrator!', false,401); 
            }
            if($user->is_verified == 2){
                return $this->response([], 'Email is not verified!', false,404); 
            }
            $token = $user->createToken('API')->accessToken;
            $user['token'] = $token;
            return $this->response($user, 'User login successfully!');
       } else {
            if($request->social_type == 'mac'){
                $user = User::where('social_id',$request->social_id)->where('social_type','mac')->first();
            }else{
                $user = User::where('email', $request->email)->first();
            }
            if($user)
            {
                $user->device_type = $request->device_type;
                $user->device_token = $request->device_token;
                $user->social_type = $request->social_type;
                $user->social_id = $request->social_id;
                $user->is_verified = 1;
                $user->save();
                $user->tokens()->delete();
                if($user->status == 2){

                    return $this->response([], 'Your account is blocked, Please contact administrator!', false,401); 
                }
                $token = $user->createToken('API')->accessToken;
                $user['token'] = $token;
                return $this->response($user, 'User login successfully!');
            } else {
                $user = new User;
                $user->name = $request->name;
                $user->email = $request->email;
                $user->social_type = $request->social_type;
                $user->social_id = $request->social_id;
                $user->device_type = $request->device_type;
                $user->device_token = $request->device_token;
                $user->is_verified = 1;
                $user->save();
                $user->assignRole([2]);
                $token = $user->createToken('API')->accessToken;
                $user['token'] = $token;
                return $this->response($user, 'User login successfully!');
            }
        
        }
        return $this->response([], $e->getMessage(), false,404);
    }

    /**
     *  @OA\Post(
     *     path="/api/profile/edit",
     *     tags={"User"},
     *     summary="Edit Profile",
     *     security={{"bearer_token":{}}},
     *     operationId="edit-profile",
     * 
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
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
    public function edit_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'name' => 'nullable|max:255',
            'photo' => 'nullable|image|mimes:svg,jpeg,jpg,gif',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,400);
        }

        try {
            $user = User::find(Auth::id());
            if ($user) {
                $user->name = $request->name;
                $user->email = $request->email;
               
                $user->save();

                // if($request->familiar_recipes != null){

                //     $f_recipes = FamiliarRacipies::where('user_id',Auth::id())->pluck('familiar_id')->toArray();
                //     $familiar_recipes = explode(",",$request->familiar_recipes);
                //     $diffs = array_merge(array_diff($f_recipes, $familiar_recipes),array_diff($familiar_recipes,$f_recipes));
                //     foreach($diffs as  $d){
                //         $fr = FamiliarRacipies::where('familiar_id',$d)->where('user_id',Auth::id())->first(); 
                //         if($fr){
                //             $fr->delete();
                //         }else{
                //             $f_rcp = new FamiliarRacipies;
                //             $f_rcp->user_id = Auth::id();
                //             $f_rcp->familiar_id = $d;
                //             $f_rcp->save();
                //         }
                //     }  
                // }
                return $this->response($user, 'Profile updated successfully!');
            }
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false,404);
        }
    }
   
     /**
     *  @OA\Post(
     *     path="/api/profile/edit/image",
     *     tags={"User"},
     *     summary="Edit Profile Image",
     *     security={{"bearer_token":{}}},
     *     operationId="edit-profile-image",
     *
     *     @OA\RequestBody(
     *        @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *                @OA\Property(
     *                    property="photo",
     *                    description="User Profile photo",
     *                    type="array",
     *                    @OA\Items(type="file", format="binary")
     *                 ),
     *		        ),
     *          ),
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
    public function edit_profile_image(Request $request)
    {
        $validator = Validator::make($request->all(), [
        
            'photo' => 'nullable|image|mimes:svg,jpeg,jpg,gif,png',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,400);
        }

        try {
            $user = User::find(Auth::id());
            if ($user) {
        
                if ($request->hasFile('photo')) {
                    $filename = null;
                    $file = $request->file('photo');
                    $filename = time() . $file->getClientOriginalName();
                    $file->move(public_path() . '/user/', $filename);
                    $user->photo = $filename;
                }
                $user->save();
                return $this->response($user, 'Profile image updated successfully!');
            }
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false,404);
        }
    }
    /**
     *  @OA\Get(
     *     path="/api/logout",
     *     tags={"User"},
     *     security={{"bearer_token":{}}},  
     *     summary="Logout",
     *     security={{"bearer_token":{}}},
     *     operationId="Logout",
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
    public function logout()
    {
        try {
            $user = User::find(Auth::id());
            $user->tokens()->delete();
            $user->device_type = null;
            $user->device_token = null;
            $user->save();
            return $this->response('', 'Logout Successfully!');
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false,404);
        }
    }

    public function allLogout()
    {
        try {
            $users = User::get();
            foreach($users as $user)
            {
                $user->tokens()->delete();
            }
            return $this->response('', 'All user logout successfully!');
        } catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }

    public function appVersion()
    {
        try {
            $appVer = AppVersion::first();
            return $this->response($appVer,'App version detail!');
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }
    /**
     *  @OA\Post(
     *     path="/api/change/password",
     *     tags={"User"},
     *     summary="Change Password",
     *     security={{"bearer_token":{}}},
     *     operationId="change-password",
     * 
     *     @OA\Parameter(
     *         name="current_password",
     *         required=true,
     *         in="query",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     * 
     *     @OA\Parameter(
     *         name="password",
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
   public function change_password(Request $request)
   {
    $validator = Validator::make($request->all(),[
        'current_password' => 'required',
        'password' => 'required|min:8',
    ]);

    if($validator->fails())
    {
        return $this->response([], $validator->errors()->first(), false);
    }

    try{
        $user = User::find(Auth::id());
        if($user){
            if(Hash::check($request->current_password,$user->password)){
                $user->password =  bcrypt($request->password);
                $user->save();
                return $this->response('','Password changed Successully!');
            }else{
                return $this->response([], 'Old password is incorrect.', false,401); 
            }   
        }
        return $this->response([], 'Enter Valid user name', false); 

    }catch(Exception $e){
        return $this->response([], $e->getMessage(), false);
    }
   }

    /**
     *  @OA\Post(
     *     path="/api/forgot/password",
     *     tags={"User"},
     *     summary="Forgot password",
     *     operationId="forgot-password",
     * 
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
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
     * )
     **/
    public function forgot_password(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->response([], $validator->errors()->first(), false,400);
        }
        $user = User::where('email', $request->email)->first();
        if (empty($user)) {
            return $this->sendError('This email not registered');
        }

        try {
            $newPass = substr(md5(time()), 0, 10);
            $user->password = bcrypt($newPass);
            $user->save();
            $data = [
                'username' => $user->user_name,
                'password' => $newPass
            ];
            $email = $user->email;
            Mail::send('mail.forgot', $data, function ($message) use ($email) {
                $message->to($email, 'test')->subject('Forgot Password');
            });
            return $this->response('', 'Email sent succesfully!');
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false,404);
        }
    }

    /**
     *  @OA\Get(
     *     path="/api/profile",
     *     tags={"User"},
     *     security={{"bearer_token":{}}},  
     *     summary="Get Login User Profile",
     *     operationId="profile",
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
    public function me()
    {
        try{
            $user = User::find(Auth::id());
            return $this->response($user, 'Profile!'); 
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false);
        }

    }

     /**
     * @OA\Get(
     *     path="/api/chefs/detail",
     *     tags={"Video"},
     *     security={{"bearer_token":{}}},  
     *     summary="Perticular chef & video details",
     *     security={{"bearer_token":{}}},
     *     operationId="chefs & video detail",
     *     
     *    @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Enter chefs ID",
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
    public function chefs_detail(Request $request){
        $validator = Validator::make($request->all(),[
            'user_id' => 'required|exists:users,id',
        ]);
        if($validator->fails())
        {
            return $this->response([], $validator->errors()->first(), false,400);
        }
		try{
                $chefs = User::where('id',$request->user_id)->where('type',1)->with(['all_uploaded_videos'])->orderBy('id','desc')->get();
				return $this->response($chefs,'Chefs details');	
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }
    /**
     *  @OA\Post(
     *     path="/api/chef/subscribe",
     *     tags={"User"},
     *     summary="Chef subcribed",
     *     security={{"bearer_token":{}}},
     *     operationId="chef-subscribed",
     * 
     *     @OA\Parameter(
     *         name="chef_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *    ),
     * 
     *    @OA\Parameter(
     *        name="status",
     *        in="query",
     *        description="1-subscribe | 2-unsubscribe",
     *        required=true,
     *        @OA\Schema(
     *            type="integer"
     *        )
     *    ),   
     * 
     *    @OA\Response(
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
    public function subscribe(Request $request){
        $validator = Validator::make($request->all(),[
            'chef_id' => 'required|exists:users,id',
            'status' => 'required|in:1,2'
        ]);
        if($validator->fails())
        {
            return $this->response([], $validator->errors()->first(), false,400);
        }
		try{
            if($request->status == 1){
                $chef = User::where('id',$request->chef_id)->where('type',1)->first();
                $learner = User::where('id',Auth::id())->where('type',2)->first();                
                if(isset($chef) && isset($learner)){
                    $check = Subscriber::where('chef_id',$request->chef_id)
                                        ->where('learner_id', Auth::id())
                                        ->first();
                    if(isset($check)){
                        return $this->response(null,'Already subscribed');	        
                    }
                    $subscribe = new Subscriber;
                    $subscribe->chef_id = $request->chef_id;
                    $subscribe->learner_id = Auth::id();
                    $subscribe->save();
                    sendPushNotification($chef->device_token,'Subscriber',Auth::user()->name.' has subscribed to you on ripe.',1,$chef->id,null);
                    return $this->response(null,'Subscribed successfully!');	        
                }
                return $this->response([], "Enter valid chef or learner!", false,404);
            }
            if($request->status == 2){
                $subscribe = Subscriber::where('chef_id',$request->chef_id)
                                    ->where('learner_id', Auth::id())
                                    ->first();
                if($subscribe){
                    $subscribe->delete();
                }
                return $this->response(null,'Unsubscribed successfully!');	        
            }
        }catch(Exception $e){
            return $this->response([], $e->getMessage(), false,404);
        }
    }

    /**
     *  @OA\Post(
     *     path="/api/otp/send",
     *     tags={"User"},
     *     summary="send otp on email",
     *     operationId="send-otp",
     * 
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *    ),
     * 
     * 
     *    @OA\Response(
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
    public function send_otp(Request $request){
        $user =  User::where('email',$request->email)->first();
        if(isset($user)){
            try {
                $otp = rand(100000,999999);
                $data = [
                    'username' => $user->name,
                    'otp' => $otp
                ];
                UserOtp::where('user_id',$user->id)->delete();
                $saveOtp = new UserOtp;
                $saveOtp->user_id = $user->id;
                $saveOtp->otp = $otp;
                $saveOtp->save();
                $email = $user->email;
                $name = $user->name;
                Mail::send('mail.otp', $data, function ($message) use ($email,$name) {
                    $message->to($email, $name)->subject('Otp');
                });
                return $this->response('', 'Otp sent succesfully!');
            } catch (Exception $e) {
                return $this->response([], $e->getMessage(), false,404);
            }
        }
         
        return $this->response([], 'This email is not registered!', false,404);
    }
    /**
     *  @OA\Post(
     *     path="/api/otp/verify",
     *     tags={"User"},
     *     summary="verify otp",
     *     operationId="verify-otp",
     * 
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *    ),
     * 
     *    @OA\Parameter(
     *         name="otp",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *    ),
     * 
     * 
     *    @OA\Response(
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
    public function verify_otp(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required',
            'otp' => 'required'
        ]);
        if($validator->fails())
        {
            return $this->response([], $validator->errors()->first(), false,400);
        }

        try {
            $user = User::where('email',$request->email)->first();
            if(isset($user)){
                $userOtp = UserOtp::where('otp',$request->otp)->where('user_id',$user->id)->first();
            }
            if(isset($userOtp)){
                $user->is_verified = 1;                
                $user->save();
                $userOtp->delete();
                $token = $user->createToken('API')->accessToken;
                $user['token'] = $token;
                return $this->response($user, 'Verified email successfully!');
            }
            return $this->response([], 'Enter valid otp or email!', false,404);            
        } catch (Exception $e) {
            return $this->response([], $e->getMessage(), false,404);
        }  
    }
}
