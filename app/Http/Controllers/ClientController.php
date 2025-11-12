<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Marchand;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/client/balance",
     *     tags={"Client"},
     *     summary="Get client account balance",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Solde récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="solde", type="number", format="float", example=1500.50),
     *                 @OA\Property(property="devise", type="string", example="XOF"),
     *                 @OA\Property(property="numeroCompte", type="string", example="CMPT-123456")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=403, description="Email non confirmé")
     * )
     */
    public function getBalance(): JsonResponse
    {
        $user = Auth::user();
        $client = $user->client;

        if (!$client || !$client->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Votre email n\'est pas confirmé.',
            ], 403);
        }

        $compte = $client->compte;

        if (!$compte) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun compte trouvé.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'solde' => $compte->solde,
                'devise' => $compte->devise,
                'numeroCompte' => $compte->numeroCompte,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/client/pay",
     *     tags={"Client"},
     *     summary="Make a payment to a merchant",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"marchand_id","montant"},
     *             @OA\Property(property="marchand_id", type="string", example="uuid-marchand"),
     *             @OA\Property(property="montant", type="number", format="float", example=500.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paiement effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Paiement effectué avec succès."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transaction_id", type="string", example="uuid-transaction"),
     *                 @OA\Property(property="nouveau_solde", type="number", format="float", example=1000.50)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Solde insuffisant"),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=403, description="Email non confirmé"),
     *     @OA\Response(response=404, description="Marchand non trouvé")
     * )
     */
    public function pay(Request $request): JsonResponse
    {
        $request->validate([
            'marchand_id' => 'required|string|exists:marchands,id',
            'montant' => 'required|numeric|min:0.01',
        ]);

        $user = Auth::user();
        $client = $user->client;

        if (!$client || !$client->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Votre email n\'est pas confirmé.',
            ], 403);
        }

        $compte = $client->compte;
        $marchand = Marchand::find($request->marchand_id);

        if (!$marchand) {
            return response()->json([
                'success' => false,
                'message' => 'Marchand non trouvé.',
            ], 404);
        }

        if (!$compte || !$compte->debiter($request->montant)) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant.',
            ], 400);
        }

        // Create transaction
        $transaction = Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'payment',
            'montant' => $request->montant,
            'devise' => $compte->devise,
            'date' => now(),
            'statut' => 'completed',
            'reference' => 'PAY-' . strtoupper(uniqid()),
            'marchand_id' => $marchand->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Paiement effectué avec succès.',
            'data' => [
                'transaction_id' => $transaction->id,
                'nouveau_solde' => $compte->solde,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/client/transfer",
     *     tags={"Client"},
     *     summary="Transfer money to another client",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"destinataire_telephone","montant"},
     *             @OA\Property(property="destinataire_telephone", type="string", example="1234567890"),
     *             @OA\Property(property="montant", type="number", format="float", example=200.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfert effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transfert effectué avec succès."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transaction_id", type="string", example="uuid-transaction"),
     *                 @OA\Property(property="nouveau_solde", type="number", format="float", example=800.50)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Solde insuffisant ou destinataire invalide"),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=403, description="Email non confirmé")
     * )
     */
    public function transfer(Request $request): JsonResponse
    {
        $request->validate([
            'destinataire_telephone' => 'required|string|exists:clients,telephone',
            'montant' => 'required|numeric|min:0.01',
        ]);

        $user = Auth::user();
        $client = $user->client;

        if (!$client || !$client->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Votre email n\'est pas confirmé.',
            ], 403);
        }

        $compte = $client->compte;
        $destinataire = Client::where('telephone', $request->destinataire_telephone)->first();

        if (!$destinataire || !$destinataire->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Destinataire invalide ou email non confirmé.',
            ], 400);
        }

        if ($destinataire->id === $client->id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas vous transférer de l\'argent à vous-même.',
            ], 400);
        }

        if (!$compte || !$compte->debiter($request->montant)) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant.',
            ], 400);
        }

        // Credit recipient
        $compteDestinataire = $destinataire->compte;
        if ($compteDestinataire) {
            $compteDestinataire->crediter($request->montant);
        }

        // Create transaction
        $transaction = Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'transfer',
            'montant' => $request->montant,
            'devise' => $compte->devise,
            'date' => now(),
            'statut' => 'completed',
            'reference' => 'TRF-' . strtoupper(uniqid()),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transfert effectué avec succès.',
            'data' => [
                'transaction_id' => $transaction->id,
                'nouveau_solde' => $compte->solde,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/client/transactions",
     *     tags={"Client"},
     *     summary="Get client transaction history",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Historique des transactions récupéré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transactions", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="string", example="uuid"),
     *                         @OA\Property(property="type", type="string", example="payment"),
     *                         @OA\Property(property="montant", type="number", format="float", example=500.00),
     *                         @OA\Property(property="devise", type="string", example="XOF"),
     *                         @OA\Property(property="date", type="string", format="date-time"),
     *                         @OA\Property(property="statut", type="string", example="completed"),
     *                         @OA\Property(property="reference", type="string", example="PAY-123456")
     *                     )
     *                 ),
     *                 @OA\Property(property="pagination", type="object",
     *                     @OA\Property(property="current_page", type="integer"),
     *                     @OA\Property(property="per_page", type="integer"),
     *                     @OA\Property(property="total", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=403, description="Email non confirmé")
     * )
     */
    public function getTransactions(Request $request): JsonResponse
    {
        $user = Auth::user();
        $client = $user->client;

        if (!$client || !$client->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Votre email n\'est pas confirmé.',
            ], 403);
        }

        $compte = $client->compte;

        if (!$compte) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun compte trouvé.',
            ], 404);
        }

        $perPage = $request->get('per_page', 10);
        $transactions = $compte->transactions()
            ->orderBy('date', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
            ],
        ]);
    }
}