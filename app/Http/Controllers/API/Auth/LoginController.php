<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Requests\UserLoginRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class LoginController
{
    public function login(UserLoginRequest $request): JsonResponse
    {
        if (Auth::attempt($request->only('email', 'password'))) {
            return response()->json(
                [
                    'user' => Auth::user(),
                    'token' => Auth::user()->createToken('TaskApp')->plainTextToken
                ],
                Response::HTTP_OK
            );
        }

        return response()->json(['message' => 'Unauthorized'], Response::HTTP_BAD_REQUEST);
    }
}
