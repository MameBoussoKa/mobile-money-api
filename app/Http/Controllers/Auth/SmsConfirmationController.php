<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfirmSmsRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Post(
 *     path="/api/confirm-sms",
 *     tags={"Auth"},
 *     summary="Confirm SMS with verification code",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"telephone","code"},
 *             @OA\Property(property="telephone", type="string", example="1234567890"),
 *             @OA\Property(property="code", type="string", example="123456")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="SMS confirmé avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="SMS confirmé avec succès. Votre compte est maintenant actif.")
 *         )
 *     ),
 *     @OA\Response(response=400, description="Code de confirmation invalide"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
class SmsConfirmationController extends Controller
{
    /**
     * Confirm SMS with verification code
     */
    public function confirm(ConfirmSmsRequest $request): JsonResponse
    {
        $data = $request->validated();

        $client = Client::where('telephone', $data['telephone'])->first();

        if (!$client || $client->confirmation_code !== $data['code']) {
            return response()->json([
                'success' => false,
                'message' => 'Code de confirmation invalide.',
            ], 400);
        }

        // Mark SMS as verified
        $client->email_verified_at = now(); // Reuse this field for SMS verification
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
            'message' => 'SMS confirmé avec succès. Votre compte est maintenant actif.',
        ]);
    }
}