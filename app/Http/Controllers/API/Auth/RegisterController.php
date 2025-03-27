<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Requests\UserRegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class RegisterController
{
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(
            [
                'user' => $user,
                'token' => $user->createToken('TaskApp')->plainTextToken,
            ],
            Response::HTTP_CREATED
        );
    }
}
