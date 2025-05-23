<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Mail\OTPVerificationMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function allUsers(): JsonResponse
    {
        return response()->json(User::all(), Response::HTTP_OK);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone_no' => $validated['phone_no'],
            'password' => Hash::make($validated['password']),
            'library_branch_id' => $validated['library_branch_id'],
        ]);

        $user->assignRole($validated['role']);
        $token = $user->createToken('API Token')->accessToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
            'token' => $token,
        ], Response::HTTP_CREATED)->cookie(
            'access_token',
            $token,
            60 * 24 * 30, // 30 days expiration
            '/',
            null,
            false,
            true,
            false,
            'Lax'
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {

        $validated = $request->validated();

        $loginField = filter_var($validated['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($loginField, $validated['login'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid login credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('API Token')->accessToken;

        $minutes = $validated['remember_me'] ? 60 * 24 * 7 : 60 * 24;

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user->load(['libraryBranch', 'roles'])),
            'token' => $token,
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['roles.permissions', 'libraryBranch']);

        return response()->json([
            'user' => new UserResource($user),
        ], Response::HTTP_OK);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->token();
        $token->revoke();
        $token->delete();

        return response()->json(['message' => 'Logged out successfully'], Response::HTTP_OK)
            ->withoutCookie('access_token');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $otp = rand(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $otp, 'created_at' => Carbon::now()]
        );

        $user = User::where('email', $request->email)->first();

        try {
            Mail::to($request->email)->send(new OTPVerificationMail(
                $otp,
                $user->username ?? $user->name ?? 'User',
                config('app.url')
            ));

            return response()->json([
                'message' => 'Mail sending attempted',
                'mail_status' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Mail sending failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->otp)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        if (Carbon::parse($record->created_at)->addMinutes(35)->isPast()) {
            return response()->json(['message' => 'OTP has expired'], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successful'], 200);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|confirmed|different:current_password',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Password changed successfully'], Response::HTTP_OK);
    }

    public function refresh(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();
        $request->user()->token()->delete();

        $token = $request->user()->createToken('API Token')->accessToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'token' => $token,
        ], Response::HTTP_OK)->cookie('access_token', $token, 60 * 24, '/', null, true, true, false, 'Strict');
    }

    public function updateUser(Request $request): JsonResponse
    {
        $user = $request->user();
     
        $validated = $request->validate([
            'username' => 'string|max:255|unique:users,username,' . $user->id,
            'email' => 'email|max:255|unique:users,email,' . $user->id,
            'phone_no' => 'nullable|string|max:20',
            'library_branch_id' => 'exists:library_branches,id',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => new UserResource($user->fresh()),
        ], Response::HTTP_OK);
    }
}
