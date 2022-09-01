<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Session;
use Hash;
use Mail;

class LearnController extends Controller
{
    public function index(Request $request)
    {
       // $users = User::query();
        $users = User::whereHas('roles', function($q){
            $q->whereIn('name', ['learn']);
            });
        if($request->search != null)
        {
            $users = $users->where('users.name','LIKE','%'.$request->search.'%')
                           ->orWhere('users.email','LIKE','%'.$request->search.'%');
        }
        
        if($request->sortby!= null && $request->sorttype)
        {
            $users = $users->orderBy($request->sortby,$request->sorttype);
        }else{
            $users = $users->orderBy('id','desc');
        }
        if($request->perPage != null){
            $users = $users->paginate($request->perPage);
        }else{
            $users = $users->paginate(10);
        }
        if($request->ajax())
        {
            return response()->json( view('admin.learner.user_data', compact('users'))->render());
        }
        return view('admin.learner.list' , compact(['users']));
    }

    public function learners(Request $request)
    {
        $columns = array( 
            0 =>'id', 
            1 =>'name',
            2 =>'email',
           
        );  
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        //$users = User::query();
        $users = User::whereHas('roles', function($q){
            $q->whereIn('name', ['learn']);
        });
        if($request->search['value'] != null){
            $users = $users->where('name','LIKE','%'.$request->search['value'].'%')
                            ->orWhere('email','LIKE','%'.$request->search['value'].'%');
        }
        if($request->length != '-1')
        {
            $users = $users->take($request->length);
        }else{
            $users = $users->take();
        }
        $users = $users->skip($request->start)
                        ->orderBy($order,$dir)
                        ->get();
       
        $data = array();
        if(!empty($users))
        {
            foreach ($users as $user)
            {
                $url = route('admin.learner.get', ['user_id' => $user->id]);
                $statusUrl = route('admin.learner.status.change', ['user_id' => $user->id]);
                $checked = $user->status == 1 ? 'checked' : '';
        
                    
                $nestedData['id'] = $user->id;
                $nestedData['name'] = $user->name;
                $nestedData['email'] = $user->email;
                $nestedData['status'] = "<div class='custom-control custom-switch'>
                                            <input type='radio' class='custom-control-input active' data-url='$statusUrl' id='active$user->id' name='active$user->id' $checked>
                                            <label class='custom-control-label' for='active$user->id'></label>
                                        </div>";
                // $nestedData['action'] = "<button class='edit-cat btn btn-outline-warning btn-sm btn-icon' data-toggle='modal' data-target='#default-example-modal' data-url=' $url '><i class='fal fa-pencil'></i></button>";
                $data[] = $nestedData;

            }
        }
        return response()->json([
            'draw' => $request->draw,
            'data' =>$data,
            'recordsTotal' => $users = User::whereHas('roles', function($q){
                                            $q->whereIn('name', ['learn']);
                                             })->count(),
            'recordsFiltered' => $request->search['value'] != null ? $users = User::whereHas('roles', function($q){
                                                                                    $q->whereIn('name', ['learn']);
                                                                                    })->count() : $users = User::whereHas('roles', function($q){
                                                                                                                $q->whereIn('name', ['learn']);
                                                                                                                })->count(),
        ]);
    }
    public function getLearner(Request $request){
        $user = User::find($request->user_id);
        return response()->json(['data'=>$user]);
    }

    public function changeStatus(Request $request){
        $user = User::find($request->user_id);
        if($user->status == 1)
        {
            $user->status = 2;
        }else{
            $user->status = 1;
        }
        $user->save();
        return response()->json(['status'=>'success']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'nullable|max:255',
            'email' => 'nullable|email',
		]);

		if($validator->fails())
		{
            return response()->json(['status'=>'error','message' => $validator->errors()->first()]);
        }

        if($request->cat_id != null)
        {
            $learner = User::find($request->cat_id);
        }else{
            $learner = new User;
        }
        try{
            $learner->name = $request->name;
            $learner->email = $request->email;
            $learner->type = 2;
            $learner->save();
            $learner->assignRole(['learn']);
            return response()->json(['status'=>'success']);
        }catch(Exception $e){
            return response()->json(['status'=>'error','message' => $e->getMessage()]);
        }
        
    }
}
