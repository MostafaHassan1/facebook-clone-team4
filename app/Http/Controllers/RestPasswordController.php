<?php

namespace App\Http\Controllers;
use App\User;
use Validator;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\RestPassword;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Mail\Message;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['RestPass']]);
    }

  
    //SignUp a new account

    public function forgetPassword(Request $REQUEST)
    {
       $code= Str::random(6);

       $validator =Validator::make($REQUEST->all(),
           [
               'email'=>'required|email:rfc,dns|exists:users',
           ]
           );
           if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
            }
        Mail::to($user)->send(new RestPassword($user->name,$code));
        
    }
    //Verifing mails 
    public function RestPass($code)
{
    $user = User::where('verif_mail',$code)->first();
    if ($user != null){
        if($user->email_verified_at==null){
        $user->update(['email_verified_at'=> now()]);
        return "Email successfuly verified";}
        
        else
            return "Email is already verified";
    }
    else 
        return "code unValid";
}

    

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}