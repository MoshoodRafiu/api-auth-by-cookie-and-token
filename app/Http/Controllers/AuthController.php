<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register()
    {
        $validator = Validator::make(\request()->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()
                                                                    ->numbers()
                                                                    ->mixedCase()
                                                                    ->symbols()
            ]
        ]);
        if ($validator->fails()){
            return Response::json(['errors' => $validator->messages()], 422);
        }
        return Response::json([]);
    }
}
