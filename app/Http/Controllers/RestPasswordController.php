<?php

namespace App\Http\Controllers;
use App\User;
use DB;
use Validator;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\RestPassword;
use Illuminate\Support\Facades\Mail;
//use Illuminate\Support\Str;
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
        $this->middleware('auth:api', ['except' => ['RestPass','forgetPassword','ConfirmPIN']]);
    }

  
   /**
     * Rest Password.
     *
     *  the password for a specific user
     *  send mail with 6 digits 
     */
    public function forgetPassword(Request $request)
    {
        //Create the 6 digit..
            $code = rand(100000, 999999);

        //make validations on the given mail to rest its password
            $validator =Validator::make($request->all(),
            [
                'email'=>'required|email:rfc,dns|exists:users',
            ]
            );
        //cheak errors
            if($validator->fails()){
             return response()->json([
                'message' => 'Check your email inbox for verification PIN'
                 ], 201);
            }
        //insert the 6 digit in the database 
            $user = User::where('email',$request->email)->update(['PassRestCode' => $code]);
        
        //Get the user from the database to view his/her name  
            $user2= User::where('email',$request->email)->where('PassRestCode' ,$code)->first();
        
        //send a mail to the user to rest the password 
            Mail::to($user2)->send(new RestPassword($user2->first_name,$code));
            return response()->json([
                'message' => 'Check your email inbox for verification PIN'
                 ], 201);
    }

    /**
     * Confirm the PIN 
     * 
     */
    public function ConfirmPIN(Request $request)
{

   $validator =Validator::make($request->all(),
            [
                'email'=>'required|email:rfc,dns|exists:users',
                'Code'=>'required',
            ]
            );
    if($validator->fails()){
        return response()->json($validator->errors()->toJson(), 402);
    } 
    
    $user2= User::where('email',$request->email)->where('PassRestCode' ,$request->Code)->first();
    
    if($user2)
    {
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false,'message'=>'inValid PIN'],422);
}

    
    /**
     * Change the password then login the user 
     * 
     */
    public function RestPass(Request $request)
{

    $user= User::where('email',$request->email)->where('PassRestCode' ,$request->Code)->first();
    
    if(! $user)
    {
        return response()->json(["error" => "PassRestCode is not valid"],422);
    }
    else {
        $password= bcrypt($request->password);
        //updating  the password value in the DB and Delete the 6 digit for security
        $user = User::where('email',$request->email)->update(['password' =>  $password ,'PassRestCode' =>  null ]);
        
        $credentials = $request->only(['email', 'password']);
        if( $token = auth()->attempt($credentials))
        {
            return $this->respondWithToken($token);
        }
        return response()->json(" Login Failed");
    }
   
}

    /**
     * change the password of a verifing loging in user
     * @return \Illuminate\Http\JsonResponse
     */
    public function changepassword(Request $request)
{
    $validator =Validator::make($request->all(),
            [
                'oldPassword'=>'required|min:8',
                'newPassword'=>'required|min:8',
                'newPassword_confirmation'=>'required|min:8|same:newPassword',
            ]
            );
    if($validator->fails()){
        return response()->json(['error' ,$validator->errors()->toJson()], 422);
    }
    $user=auth()->user();
    $user->password = bcrypt($request->oldPassword);
    if( ! $user)
    {
        return response()->json(["error" => "old password is incorect"],422);
    }
   //else if ($user) return $user->first_name;
    else if($request->newPassword != $request->newPassword_confirmation)
    {
        return response()->json(["error" => "the confirmation of the new password is incorect"],422);
    }
    else if($user->bcrypt(password) == bcrypt($request->newPassword))
    {
        return response()->json(["error" => "the new password is the same as the old one"],422);
    }
    $user->update(['password'=> bcrypt( $request->newPassword)]);

    return response()->json(["message" => "the password successfuly changed"],201);
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
// auth()->user(); btgeb el token bta3 el user 
