<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Compte;
use App\Models\Marchand;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/compte/{id}/solde",
     *     tags={"Compte"},
     *     summary="Get client account balance",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
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
     *     @OA\Response(response=403, description="Email non confirmé"),
     *     @OA\Response(response=404, description="Compte non trouvé")
     * )
     */
    public function getSolde($id): JsonResponse
    {
        $user = Auth::user();
        $client = $user->client;

        if (!$client || !$client->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Votre email n\'est pas confirmé.',
            ], 403);
        }

        $compte = Compte::find($id);

        if (!$compte) {
            return response()->json([
                'success' => false,
                'message' => 'Compte non trouvé.',
            ], 404);
        }

        // Check if the compte belongs to the authenticated client
        if ($compte->client_id !== $client->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce compte.',
            ], 403);
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
     *     path="/api/compte/{id}/pay",
     *     tags={"Compte"},
     *     summary="Make a payment to a recipient",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"montant"},
     *             @OA\Property(property="recipient_telephone", type="string", example="1234567890"),
     *             @OA\Property(property="marchand_code", type="string", example="MARCHAND-123"),
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
     *                 @OA\Property(property="nouveau_solde", type="number", format="float", example=500.00)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Solde insuffisant ou destinataire invalide"),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=403, description="Email non confirmé"),
     *     @OA\Response(response=404, description="Compte non trouvé")
     * )
     */
    public function pay($id, Request $request): JsonResponse
    {
        $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'recipient_telephone' => 'nullable|string|exists:clients,telephone',
            'marchand_code' => 'nullable|string|exists:marchands,codeMarchand',
        ]);

        // Ensure only one recipient type is provided
        if (!$request->recipient_telephone && !$request->marchand_code) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez fournir un numéro de téléphone destinataire ou un code marchand.',
            ], 400);
        }

        if ($request->recipient_telephone && $request->marchand_code) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez fournir soit un numéro de téléphone, soit un code marchand, pas les deux.',
            ], 400);
        }

        $user = Auth::user();
        $client = $user->client;

        if (!$client || !$client->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Votre email n\'est pas confirmé.',
            ], 403);
        }

        $compte = Compte::find($id);

        if (!$compte) {
            return response()->json([
                'success' => false,
                'message' => 'Compte non trouvé.',
            ], 404);
        }

        // Check if the compte belongs to the authenticated client
        if ($compte->client_id !== $client->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce compte.',
            ], 403);
        }

        $recipientType = null;
        $recipientId = null;

        if ($request->recipient_telephone) {
            $recipient = Client::where('telephone', $request->recipient_telephone)->first();
            if (!$recipient || !$recipient->email_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destinataire invalide ou email non confirmé.',
                ], 400);
            }
            if ($recipient->id === $client->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas vous payer à vous-même.',
                ], 400);
            }
            $recipientType = 'client';
            $recipientId = $recipient->id;
        } elseif ($request->marchand_code) {
            $recipient = Marchand::where('codeMarchand', $request->marchand_code)->first();
            if (!$recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Marchand non trouvé.',
                ], 404);
            }
            $recipientType = 'marchand';
            $recipientId = $recipient->id;
        }

        // Vérifier si le solde est suffisant avant de procéder au paiement
        if ($compte->solde < $request->montant) {
            return response()->json([
                'success' => false,
                'message' => 'Montant insuffisant.',
            ], 400);
        }

        if (!$compte->debiter($request->montant)) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du débit du compte.',
            ], 400);
        }

        // Credit recipient if it's a client-to-client payment
        if ($recipientType === 'client') {
            $recipientClient = Client::find($recipientId);
            if ($recipientClient && $recipientClient->compte) {
                $recipientClient->compte->crediter($request->montant);
            }
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
            'marchand_id' => $recipientType === 'marchand' ? $recipientId : null,
            'recipient_type' => $recipientType,
            'recipient_id' => $recipientId,
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
     *     path="/api/compte/{id}/transfer",
     *     tags={"Compte"},
     *     summary="Transfer money to another client",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
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
     *     @OA\Response(response=403, description="Email non confirmé"),
     *     @OA\Response(response=404, description="Compte non trouvé")
     * )
     */
    public function transfer($id, Request $request): JsonResponse
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

        $compte = Compte::find($id);

        if (!$compte) {
            return response()->json([
                'success' => false,
                'message' => 'Compte non trouvé.',
            ], 404);
        }

        // Check if the compte belongs to the authenticated client
        if ($compte->client_id !== $client->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce compte.',
            ], 403);
        }

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
            'recipient_type' => 'client',
            'recipient_id' => $destinataire->id,
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
     *     path="/api/compte/{id}/transactions",
     *     tags={"Compte"},
     *     summary="Get client transaction history",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
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
     *     @OA\Response(response=403, description="Email non confirmé"),
     *     @OA\Response(response=404, description="Compte non trouvé")
     * )
     */
    public function getTransactions($id, Request $request): JsonResponse
    {
        $user = Auth::user();
        $client = $user->client;

        if (!$client || !$client->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Votre email n\'est pas confirmé.',
            ], 403);
        }

        $compte = Compte::find($id);

        if (!$compte) {
            return response()->json([
                'success' => false,
                'message' => 'Compte non trouvé.',
            ], 404);
        }

        // Check if the compte belongs to the authenticated client
        if ($compte->client_id !== $client->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé à ce compte.',
            ], 403);
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