<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'name'     => 'required'
            // 'device_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'status' => false ]);
        }
 
        $user = User::create(
            ['password' => Hash::make($request->password), 'email' => $request->email, 'name' => $request->name ]
        );

        if (!$user || ! Hash::check($request->password, $user->password)) {

            return response()->json(['error' => 'The provided credentials are incorrect.', 'status' => false ]);
            
        }

        $token =  $user->createToken(Str::random(20))->plainTextToken;
        $user->token = $token;
        return response()->json(['token' => $token, 'user' => $user, 'status' => true]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            // 'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(), 'status' => false ], 422);
        }

        $user = User::where(
            ['email' => $request->email]
        )->first();

        if (!$user || ! Hash::check($request->password, $user->password)) {

            return response()->json(['error' => 'The provided credentials are incorrect.' ], 422);
            
        }

        $user->save();

        $token =  $user->createToken(Str::random(20))->plainTextToken;
        $user->token = $token;
        return response()->json(['token' => $token, 'user' => $user, 'status' => true]);
    }

    // public function getUser(Request $request)
    // {
    //     $users = User::paginate(15);


    //     return response()->json(['users' => $users, 'status' => true]);

    // }

    
    
}
