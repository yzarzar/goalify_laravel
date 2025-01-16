<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UploadImageRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends BaseController
{
    /**
     * Show the authenticated user's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showProfile()
    {
        try {
            $user = JWTAuth::user();

            return $this->sendSuccess(
                new UserResource($user),
                'User profile fetched successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Unable to fetch profile',
                ['error' => 'Failed to retrieve user information'],
                500,
                [],
                $e
            );
        }
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param UpdateUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(UpdateUserRequest $request)
    {
        try {
            $user = JWTAuth::user();
            $data = $request->validated();

            // Handle password update separately
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
                unset($data['current_password']);
            }

            $user->update($data);

            return $this->sendSuccess(
                new UserResource($user),
                'User profile updated successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Unable to update profile',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    /**
     * Upload the authenticated user's profile picture.
     *
     * @param UploadImageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(UploadImageRequest $request)
    {
        try {
            $user = JWTAuth::user();

            if ($request->hasFile('profile_picture')) {
                // Delete existing image if it exists
                if ($user->profile_picture) {
                    $oldImagePath = public_path('profilePictures/' . $user->profile_picture);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $imageName = time() . '.' . $request->profile_picture->getClientOriginalExtension();
                $request->profile_picture->move(public_path('profilePictures'), $imageName);
                $user->profile_picture = $imageName;
                $user->save();
            }

            return $this->sendSuccess(
                new UserResource($user),
                'Profile picture uploaded successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError(
                'Unable to upload profile picture',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
