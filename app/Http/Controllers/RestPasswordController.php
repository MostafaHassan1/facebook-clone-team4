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
       $code= integer::random(6);

       $validator =Validator::make($REQUEST->all(),
           [
               'email'=>'required|email:rfc,dns|exists:users',
           ]
           );
           if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
            }
            $user = DB::table('users')->where('email',$REQUEST->email)->update(['PassRestCode' => $code]);
            Mail::to($user)->send(new RestPassword($user->name,$code));
        
        
    }
    //Verifing mails 
    public function RestPass($code, $email)
{
    $user = User::where('PassRestCode',$code)->where('email',$email);

    
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