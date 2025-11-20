<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Post(
 *     path="/api/login",
 *     tags={"Auth"},
 *     summary="Login client",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"phone_number","password"},
 *             @OA\Property(property="phone_number", type="string", example="785942490"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Connexion réussie",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Connexion réussie."),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="token", type="string", example="bearer_token_here"),
 *                 @OA\Property(property="refresh_token", type="string", example="refresh_token_here")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Identifiants invalides"),
 *     @OA\Response(response=422, description="Validation error")
 * ),
 * @OA\Post(
 *     path="/api/logout",
 *     tags={"Auth"},
 *     summary="Logout client",
 *     security={{"passport": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Déconnexion réussie",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Déconnexion réussie.")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Non autorisé")
 * )
 */
class LoginController extends Controller
{
    /**
     * Login client and return token
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string',
            'password' => 'required|string',
        ]);

        $client = Client::where('telephone', $request->phone_number)->first();

        if (!$client || !$client->user) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found.',
            ], 401);
        }

        if (!Hash::check($request->password, $client->user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password.',
            ], 401);
        }

        if (!$client->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Votre email n\'est pas confirmé.',
            ], 403);
        }

        // Create proper token
        $token = $client->user->createToken('API Token')->accessToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie.',
            'data' => [
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout client and revoke token
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user() && $request->user()->token()) {
            $request->user()->token()->revoke();
        }

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }
}