<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthController extends BaseController
{
    /**
     * Register a new user.
     *
     * @param CreateUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(CreateUserRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => strtolower($request->email),
                'password' => Hash::make($request->password),
            ]);

            $token = JWTAuth::fromUser($user);

            return $this->sendCreated([
                'user' => new UserResource($user),
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ], 'User registered successfully');
        } catch (\Exception $e) {
            return $this->sendError(
                'Registration failed',
                ['error' => 'Could not create user account'],
                500,
                [],
                $e
            );
        }
    }

    /**
     * Login a user and return a JWT token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->sendError(
                    'Unauthorized',
                    ['error' => 'Invalid email or password'],
                    401
                );
            }

            $user = JWTAuth::user();

            return $this->sendSuccess([
                'user' => new UserResource($user),
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ], 'Login successful');
        } catch (JWTException $e) {
            return $this->sendError(
                'Could not create token',
                ['error' => 'Login failed due to token generation issue'],
                500,
                [],
                $e
            );
        }
    }

    /**
     * Logout the authenticated user by invalidating the JWT token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return $this->sendSuccess(
                null,
                'Logout successful'
            );
        } catch (JWTException $e) {
            return $this->sendError(
                'Logout failed',
                ['error' => 'Failed to invalidate token'],
                500,
                [],
                $e
            );
        }
    }
}
