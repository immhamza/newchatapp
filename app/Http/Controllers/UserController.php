<?php

namespace App\Http\Controllers;

use App\Message;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $auth = auth()->user();
        $users = (new User)->newQuery();
        $users->select('users.*',
            DB::raw("(SELECT count(messages.id)
                        FROM messages
                        WHERE messages.sender_id = users.id
                          AND messages.recipient_id = $auth->id
                          AND messages.seen = '0' ) as unseen_msg_cnt"))
            ->where('users.id', '<>', auth()->id());

        return response()->json($users->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $image= $request ->file('avatar');
        $my_image=rand().'.'.$image->getClientOrigninalExtension();
        $image ->move(public_path('/images/'),$my_image);




        User::create([

            'name'=>$request->name,
            'email'=>$request->email,
            'image'=>$my_image,  
            ]);


    }


    /**
     * Display the specified resource.
     *
     * @param \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        if(Auth::user()){
            $user = User::find(Auth::user()->id);

            if($user){
                return view('user.edit')->withUser($user);
            }else{
                return redirect()->back();
            }
        }else{
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $avatarname = $request-> pic;

        $image =$request->file('avatar');
        
        if($image!=""){

            $request->validate([
                "name"=>"required",
                "email"=>"required",
                "avatar"=>"image",   
            ]);
                $avatarname = time() .'.' . $image ->getClientOriginalExtension();
                $avatarpath = public_path('/images/');
                $image->move($avatarpath, $avatarname);


        }else{

           
            $request->validate([
                "name"=>"required",
                "email"=>"required",
                "image"=>"image",   
            ]);
            
        }

        $user = User::find(Auth::user()->id);

        if ($user){
            $validate =null;
            if(Auth::user()->email === $request['email']){
                $validate  =$request->validate([
                    'name' => 'required|min:2',
                    'email' =>'required|email'
                ]);


            }else{
                $validate  =$request->validate([
                    'name' => 'required|min:2',
                    'email' =>'required|email|unique:users'
                ]);

            }

            if($validate){
            $user->name = $request['name'];
            $user->email = $request['email'];
            $user->avatar =  $avatarname;

            $user->save();
            $request->session()->flash('success','Your details have now been updated');
            return redirect()->back();
            }else{
                return redirect()->back();
            }
        }else{
            return redirect()->back();
        }
        }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }

    /**
     * get Messages between auth and user
     * @param User $user
     * @return JsonResponse
     */
    public function messages_between(User $user)
    {
        $messages = auth()->user()->messages_between($user);
        return response()->json($messages);
    }

    /**
     * make the income messages seen
     *
     * @param User $user
     * @return JsonResponse
     */
    public function markMessagesSeen(User $user)
    {
        auth()->user()->markMessagesSeen($user);
        return response()->json([
            'seen' => '1'
        ]);
    }

    /**
     * get the number of unseen message between auth and user
     *
     * @param User $user
     * @return
     */
    public function unseenMessagesCount(User $user)
    {
        $mCount = Message::where(['recipient_id' => auth()->id(), 'sender_id' => $user->id, 'seen' => '0'])
            ->count();
        return response()->json([
            'mCount' => $mCount
        ]);
    }


    public function passwordEdit(){

        if(Auth::user()){
          
            $user = User::find(Auth::user()->id);
            if($user){
                return view('user.password');
            }
            
        }else{
            return redirect()->back();
        }

       
    }

    public function passwordUpdate(Request $request){

        $validate  =$request->validate([
            'oldPassword' => 'required|min:7',
            'password' =>'required|min:7|required_with:password_confirmation',
             
        ]);


            $user = User::find(Auth::user()->id);

            if($user){
                
                if(Hash::check($request['oldPassword'], $user->password) && $validate){

                    $user->password=Hash::make($request['password']);

                    $user->save();

                    $request->session()->flash('success','Your password has been updated');
                    return redirect()->back();
                }else{
                    $request->session()->flash('error','Your password didnot match');
                    return redirect()->back('passwordedit');
                }

            }

    }

}
