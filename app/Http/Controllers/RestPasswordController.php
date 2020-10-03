<?php

namespace App\Http\Controllers;
use App\User;
use Validator;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\VeriyEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Mail\Message;

class RestPasswordController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['RestPass','forgetPassword']]);
    }

  
   /**
     * Rest Password.
     *
     *  the password for a specific user
     *  send mail with 6 digits 
     */

    public function forgetPassword(Request $REQUEST)
    {
        
        //Create the 6 digit..
        //something wrong !!!!??????
            $code= Str::random(6);
        
        // $code = mt_rand(100000, 999999);

        //make validations on the given mail to rest its password
            $validator =Validator::make($REQUEST->all(),
            [
                'email'=>'required|email:rfc,dns|exists:users',
            ]
            );
            
        //cheak errors
            if($validator->fails()){
             return response()->json($validator->errors()->toJson(), 402);
            }

        //insert the 6 digit in the database
            $user = User::where('email',$REQUEST->email)->update(['PassRestCode' => $code]);
        //send a mail to the user to rest the password
            Mail::to($user)->send(new RestPassword($user->name,$code));

    }
    /**
     * Verifing mails 
     *  @param  integer $code
     * @param string $email
     * هيروحلها لما يدخل ال 6 ارقام صح
     */
    public function RestPass($code, $email)
{
    $user = User::where('PassRestCode',$code)->where('email',$email);

    //Delete the 6 digit for security
    $user = User::where('email',$REQUEST->email)->update(['PassRestCode' =>  null ]);
    
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
