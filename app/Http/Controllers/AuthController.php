<?php

namespace App\Http\Controllers;

use App\Jobs\EmailVerificationJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['login', 'register']);
    }
    public function register($type = 'token'): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make(\request()->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'string', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()->mixedCase()->symbols()],
            'token_name' => ['sometimes', 'string']
        ]);
        if ($validator->fails()){
            return Response::json(['errors' => $validator->messages()], 422);
        }
        if (!in_array($type, ['token', 'spa'])){
            return Response::json(['errors' => 'invalid url'], 422);
        }
        $emailToken = sha1(request()->get('email'));
        $user = User::query()->create([
            'name' => request()->get('name'),
            'email' => request()->get('email'),
            'password' => Hash::make(request()->get('password')),
            'email_verification_token' => Hash::make($emailToken),
            'email_verification_token_expiry' => now()->addMinutes(30)
        ]);
        EmailVerificationJob::dispatch($user, $emailToken);
        return $this->returnDataWithTokenOrUser($type, $user);
    }

    public function login($type = 'token'): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make(\request()->all(), [
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);
        if ($validator->fails()){
            return Response::json(['errors' => $validator->messages()], 422);
        }
        if (!in_array($type, ['token', 'spa'])){
            return Response::json(['errors' => 'invalid url'], 422);
        }
        if (Auth::attempt(request()->only('email', 'password'))){
            $user = User::query()->where('email', request()->get('email'))->first();
            return $this->returnDataWithTokenOrUser($type, $user);
        }
        return Response::json(['message' => 'Invalid login credentials'], 400);
    }

    public function logout(Request $request, $type = 'token'): \Illuminate\Http\JsonResponse
    {
        if($type == 'token'){
            $request->user()->currentAccessToken()->delete();
        }else{
            Auth::guard('web')->logout();
        }
        return Response::json(['message' => 'Logged out successfully']);
    }

    private function returnDataWithTokenOrUser($type, $user): \Illuminate\Http\JsonResponse
    {
        if ($type == 'token'){
            $token = $user->createToken(request()->get('token_name') ?? 'app')->plainTextToken;
            return Response::json(['data' => ['user' => $user, 'token' => $token]]);
        }else{
            Auth::attempt(['email' => $user['email'], 'password' => request()->get('password')]);
            return Response::json(['data' => ['user' => Auth::user()]]);
        }
    }
}
