<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterClientRequest;
use App\Services\SmsService;
use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(title="Mobile Money API", version="1.0")
 */

/**
 * @OA\Post(
 *     path="/api/register",
 *     tags={"Auth"},
 *     summary="Register a new client",
 * @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"username","password","nom","prenom","telephone"},
 *             @OA\Property(property="username", type="string", example="john_doe"),
 *             @OA\Property(property="password", type="string", example="password123"),
 *             @OA\Property(property="nom", type="string", example="Doe"),
 *             @OA\Property(property="prenom", type="string", example="John"),
 *             @OA\Property(property="telephone", type="string", example="1234567890")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Inscription réussie. Un SMS de confirmation est envoyé.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Inscription réussie. Un SMS de confirmation a été envoyé."),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="user_id", type="integer", example=1),
 *                 @OA\Property(property="client_id", type="integer", example=1)
 *             )
 *         )
 *     ),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */

class RegisterController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
      * Register a new client and send confirmation code by SMS
      */
    public function register(RegisterClientRequest $request): JsonResponse
    {
        $data = $request->validated();

        // create user
        $user = User::create([
            'username' => $data['username'],
            'password' => $data['password'], // User model casts to hashed
            'role' => 'client',
            'langue' => 'fr',
            'theme' => 'light',
        ]);

        // generate confirmation code
        $code = (string) mt_rand(100000, 999999);

        // create client linked to user
        $client = $user->client()->create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'telephone' => $data['telephone'],
            'confirmation_code' => $code,
        ]);

        // send confirmation SMS
        $sent = $this->smsService->sendConfirmationCode($client->telephone, $client->nom . ' ' . $client->prenom, $code);

        return response()->json([
            'success' => $sent,
            'message' => $sent ? 'Inscription réussie. Un SMS de confirmation a été envoyé.' : 'Inscription créée, mais l\'envoi du SMS a échoué.',
            'data' => [
                'user_id' => $user->id,
                'client_id' => $client->id,
            ],
        ], $sent ? 201 : 201);
    }
}
