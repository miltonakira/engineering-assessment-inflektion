<?php

// This is the namespace declaration for the controller
namespace App\Http\Controllers;

// Importing the necessary classes and facades
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Illuminate\Routing\Controller;
use App\Models\User;

/**
 * Control the authentication on api..or others?...
 */
class AuthController extends Controller
{
    /**
     * Authentication of users
     * 
     * @param  Request $request - Requested received by post in raw data (json).
     */
    public function authenticate(Request $request)
    {
        // Extracting the email and password from the request
        $credentials = $request->only('email', 'password');

        try {
            // Attempting to authenticate the user using JWTAuth
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            // If there's an error creating the token, return a 500 Internal Server Error response
            return response()->json(['error' => $e->getMessage()], 500);
        }

        // return the generated token if success
        return response()->json(['token' => $token]);
    }

    /**
     * Register a new user.
     *
     * @param  Request $request - Requested received by post in raw data (json).
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
          // Validate the request data
          $validatedData = $request->validate([
              'name' => 'required|string|max:255',
              'email' => 'required|string|email|max:255|unique:users',
              'password' => 'required|string|min:6',
          ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
          return response()->json(['ValidationError' => $e->errors()], 500);
        }

        // Create a new user instance
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // Generate a JWT token for the user
        $token = JWTAuth::fromUser($user);

        // Return the token and user data
        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Get the authenticated user
     */
    public function getUser()
    {
        // Using the Auth facade to get the authenticated user
        return response()->json(Auth::user());
    }
}