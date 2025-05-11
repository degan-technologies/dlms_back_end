<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        // Get validated data
        $validated = $request->validated();

        // Create the user with validated data
        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone_no' => $validated['phone_no'],
            'password' => Hash::make($validated['password']),
            'library_branch_id' => $validated['library_branch_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
        ]);

        // Assign the role to the user
        $user->assignRole($validated['role']);

        // Generate token for the user
        $token = $user->createToken('API Token')->accessToken;

        // Return response with token and cookie
        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
            'token' => $token,
        ], Response::HTTP_CREATED)
            ->cookie('access_token', $token, 60 * 24, null, null, true, true, false, 'Strict');
    }

    /**
     * Login user with username or email
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        // Determine if login is email or username
        $loginField = filter_var($validated['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Find the user by email or username
        $user = User::where($loginField, $validated['login'])->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid login credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Generate token
        $token = $user->createToken('API Token')->accessToken;

        // Set cookie expiration (in minutes) - 1 day default or longer if remember_me is true
        $minutes = $validated['remember_me'] ? 60 * 24 * 7 : 60 * 24;

        // Return response with token and cookie
        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user->load(['libraryBranch', 'roles'])),
            'token' => $token,
        ], Response::HTTP_OK)
            ->cookie('access_token', $token, $minutes, '/', null, true, true, false, 'Strict');
    }

    /**
     * Get authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        // Load relationships for the resource
        $user = $request->user();
        $user->load(['roles.permissions', 'libraryBranch']);

        return response()->json([
            'user' => new UserResource($user),
        ], Response::HTTP_OK);
    }

    /**
     * Logout user (revoke and clear cookie)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Get access token and revoke it
        $token = $request->user()->token();
        $token->revoke();
        $token->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ], Response::HTTP_OK)
            ->withoutCookie('access_token');
    }

    /**
     * Send password reset link
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to your email',
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => 'Unable to send password reset link',
            'errors' => ['email' => [__($status)]],
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Reset password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password reset successfully',
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => 'Unable to reset password',
            'errors' => ['email' => [__($status)]],
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Change user password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully',
        ], Response::HTTP_OK);
    }

    /**
     * Refresh the user's token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        // Revoke current token
        $request->user()->token()->revoke();
        $request->user()->token()->delete();

        // Create new token
        $token = $request->user()->createToken('API Token')->accessToken;

        return response()->json([
            'message' => 'Token refreshed',
            'token' => $token,
        ], Response::HTTP_OK)
            ->cookie('access_token', $token, 60 * 24, '/', null, true, true, false, 'Strict');
    }
}
