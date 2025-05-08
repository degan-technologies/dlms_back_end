<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\PersonalAccessTokenResult;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users',
            'email' => 'nullable|email|unique:users',
            'phone_no' => 'required|string|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'library_branch_id' => 'required|exists:library_branches,id',

        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'phone_no' => $request->phone_no,
            'password' => Hash::make($request->password),
            'library_branch_id' => $request->library_branch_id,
        ]);

        $user->assignRole($request->role);

        $token = $user->createToken('API Token')->accessToken;

        return response()->json(['token' => $token], 201)
            ->cookie('access_token', $token, 60 * 24, null, null, true, true, false, 'Strict');
    }

    // Login user
    public function login(Request $request)
{
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    $user = User::where('username', $request->username)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $token = $user->createToken('API Token')->accessToken;
    $cookie = cookie(
        'access_token', $token, 60 * 24, // 1 day
        '/', null, true, true, false, 'Strict'
    );

    return response()->json(['token' => $token])
        ->withCookie(
            cookie(
                'access_token', $token, 60 * 24, // 1 day
                '/', null, true, true, false, 'Strict'
            )
        );
}

    // Get authenticated user
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    // Logout user (revoke and clear cookie)
    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        $token->delete();

        return response()->json(['message' => 'Successfully logged out'])
            ->withoutCookie('access_token');
    }
}
