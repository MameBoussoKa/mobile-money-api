<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmEmailRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Post(
 *     path="/api/confirm-email",
 *     tags={"Auth"},
 *     summary="Confirm email with verification code",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","code"},
 *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *             @OA\Property(property="code", type="string", example="123456")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Email confirmé avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Email confirmé avec succès. Votre compte est maintenant actif.")
 *         )
 *     ),
 *     @OA\Response(response=400, description="Code de confirmation invalide"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
class EmailConfirmationController extends Controller
{
    /**
     * Confirm email with verification code
     */
    public function confirm(ConfirmEmailRequest $request): JsonResponse
    {
        $data = $request->validated();

        $client = Client::where('email', $data['email'])->first();

        if (!$client || $client->confirmation_code !== $data['code']) {
            return response()->json([
                'success' => false,
                'message' => 'Code de confirmation invalide.',
            ], 400);
        }

        // Mark email as verified
        $client->email_verified_at = now();
        $client->confirmation_code = null; // Clear the code after use
        $client->save();

        // Create account for the client if not exists
        if (!$client->compte) {
            $compte = new \App\Models\Compte([
                'numeroCompte' => 'CMPT-' . strtoupper(uniqid()),
                'solde' => 0.00,
                'devise' => 'XOF',
                'dateDerniereMaj' => now(),
            ]);
            $compte->id = (string) \Illuminate\Support\Str::uuid();
            $client->compte()->save($compte);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email confirmé avec succès. Votre compte est maintenant actif.',
        ]);
    }
}