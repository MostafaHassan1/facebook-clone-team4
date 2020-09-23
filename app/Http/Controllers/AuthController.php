<?php

namespace App\Http\Controllers;
use App\User;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Notifications\VerifyEmail;
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

  
    //SignUp a new account

    public function register(Request $REQUEST)
    {
       
       $validator =Validator::make($REQUEST->all(),
           [
               'first_name'=>'required|max:225',
               'last_name'=>'required|max:225',
               'email'=>'required|email:rfc,dns|unique:users',
               'password'=>'required|min:8',
               'gender'=>'required',
               'birthdate'=>'required|date',
           ]
           );
           if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
            }
         
            $user = User::create(array_merge(
                $validator->validated(),
                ['password' => bcrypt($REQUEST->password)]
            ));

        return response()->json([
            'message' => 'Check your email inbox for verification link'
             ], 201);
        
    }
    //Verifing mails Hbdaya w yarab tzbot
    public function verification_email()
{
    Notification::fake();

    $userData = factory('App\Models\User')->make(['email_verified_at' => null]);
    $this->post(route('api.register'), $userData);

    Notification::assertSentTo(User::latest()->first(), VerifyEmail::class);
}

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        /*if(! verification_email()){
            return "E-mail must be verified";
        }*/
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'something unValid mail or password'], 401);
        }

        return $this->respondWithToken($token);
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