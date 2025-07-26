<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        return ApiResponse::handle(function() use ($request) {

            $validator = Validator::make($request->all(), [
                'name' => ['bail', 'required'],
                'email' => ['bail', 'required', 'string', 'email', 'unique:users,email'],
                'password' => ['bail', 'required'],
                'confirmPassword' => ['bail', 'required', 'same:password']
            ]);
    
            if ($validator->fails()){
                throw new ValidationException($validator);
            }

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $token = $user->createToken('registerToken')->plainTextToken;
            $success['name'] =  $user->name;
            $success['email'] = $user->email;
            $success['token'] = $token;
    
            return $success;
        });
    }

    public function login(Request $request): JsonResponse
    {
        return ApiResponse::handle(function() use ($request) {

            $validator = Validator::make($request->all(), [
                'email'    => ['bail', 'required', 'string', 'email', 'exists:users,email'],
                'password' => ['bail', 'required', 'string']
            ]);
    
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
    
            $user = User::where('email', $request->email)->first();
    
            // check if the user exists and the provided password is correct.
            if (!$user || !Hash::check($request->password, $user->password)) {
                throw new Exception('Invalid credentials');
            }
    
            // generate a token for the authenticated user.
            $token = $user->createToken('loginToken')->plainTextToken;
    
            // return the user data and token in the response
            return [
                'user'   => $user,
                'token'  => $token,
            ];
        });
    }

    public function logout(Request $request): JsonResponse
    {
        return ApiResponse::handle(function() use ($request) {

            $user = $request->user();

            if (!$user) {
                throw new Exception('User not authenticated');
            }
    
            // revoke the current access token.
            $user->currentAccessToken()->delete();
    
            return 'Logged out successfully';
        });
    }
}
