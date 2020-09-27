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


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','verif_email']]);
    }

  
    //SignUp a new account

    public function register(Request $REQUEST)
    {
       $verification_code= Str::random(50);

       $validator =Validator::make($REQUEST->all(),
           [
               'first_name'=>'required|string|min:3|max:12',
               'last_name'=>'required|string|min:3|max:12',
               'email'=>'required|email:rfc,dns|unique:users',
               'password'=>'required|min:8',
               'gender'=>'required|boolean',
               'birthdate'=>'required|date',
           ]
           );
           if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
            }
         
            $user = User::create(array_merge(
                $validator->validated(),
                ['password' => bcrypt($REQUEST->password),'verif_mail'=>$verification_code]
            ));
        Mail::to($user)->send(new VeriyEmail($user->name,$verification_code));

        return response()->json([
            'message' => 'Check your email inbox for verification link'
             ], 201);
        
    }
    //Verifing mails 
    public function verif_email($code)
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
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        $rules = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required',
        ]);
            
        if($rules->fails()) {
            return response()->json(['success'=> false, 'error'=> $rules->messages()], 401);
        }

       if (! $token = auth()->attempt($credentials)) {
            return response()->json(['success' => false, 
            'error' => 'Wrong credintials, Please try to login with a valid e-mail and password.']
            , 401);
        }

        if(auth()->user()->email_verified_at != null)
        {
            return $this->respondWithToken($token);
        }
        else
            return response()->json('Please check your email inbox for verfication email', 401);
           
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
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