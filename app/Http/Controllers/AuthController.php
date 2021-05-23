<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make(\request()->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'string', 'unique:users,email'],
            'password' => ['required', 'confirmed',
                            Password::min(8)->letters()
                                                ->numbers()
                                                ->mixedCase()
                                                ->symbols()],
            'token_name' => ['sometimes', 'string']
        ]);
        if ($validator->fails()){
            return Response::json(['errors' => $validator->messages()], 422);
        }
        $token = sha1(request()->get('email'));
        $user = User::query()->create([
            'name' => request()->get('name'),
            'email' => request()->get('email'),
            'password' => Hash::make(request()->get('password')),
            'email_verification_token' => Hash::make($token),
            'email_verification_token_expiry' => now()->addMinutes(30)
        ]);
        NotificationController::sendEmailVerificationLink($user, $token);
        $token = $user->createToken(request()->get('token_name') ?? 'app')->plainTextToken;
        return Response::json(['data' => ['user' => $user, 'token' => $token]]);
    }
}
