<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Ulluminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Validator;

class AuthController extends Controller {
    public function __construct() {
    	$this->user = new User;
    }

    public function index() {   
        $users = $this->user->paginate(10);
        return $users;
    }

    public function register( Request $request ) {
        $rules = [
            'email'=>'required|string|unique:users,email|email',
            'password'=>'required|string'
        ];

        $validator = Validator::make( $request->all(), $rules );

        if ( $validator->fails() ) {
            return response()->json( [ 'message' => $validator->messages()->first() ], 400 );
        } else {
            $this->user->email    = $request->email;
            $this->user->password = $request->password;

            $user = $this->user->create([
                'email'    => $request->email,
                'password' => bcrypt( $request->password ),
            ]);

            return response()->json( [ 'message' => 'User successfully registered' ], 201 );
        }
    }

    public function login( Request $request ) {
        $this->checkFailedAttempts();

        $throttleKey = 'login' . $request->email;
        $maxAttempts = 5;
        $lockout     = 300;
        $rules = [
            'email'=>'required|string|email',
            'password'=>'required|string',
        ];

        $validator = Validator::make( $request->all(), $rules );

        if ( $validator->fails() ) {
            return response()->json( [ 'message' => $validator->messages()->first() ], 400 );
        } else {
            $user = $this->user->where( 'email', $request->email )->first();

            if ( ! Auth::attempt( $request->only( [ 'email', 'password' ] ) ) ) {
                return response()->json( ['message' => 'Invalid credentials'], 401 );
            }

            $token = $user->createToken('Auth-token')->plainTextToken;

            if ( ! empty( $token ) ) {
                return response()->json( [ 'access_token' => $token ], 201 );
            }
        }
    }

    public function logout( Request $request ) {
        auth()->user()->tokens()->delete();

        return response( [ 'message' => 'Logged out' ] );
    }
}
