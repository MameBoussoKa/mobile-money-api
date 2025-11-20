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
        $this->middleware('auth:api');
    }

    /**
     * @OA\Get(
     *     path="/api/compte/{id}/solde",
     *     tags={"Compte"},
     *     summary="Get client account balance",
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solde récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="solde", type="number", format="float", example=150000),
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

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $client = $user->client;

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé.',
            ], 404);
        }

        if (!$client->email_verified_at) {
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
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"montant"},
     *             @OA\Property(property="recipient_telephone", type="string", example="775942400"),
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

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $client = $user->client;

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé.',
            ], 404);
        }

        if (!$client->email_verified_at) {
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


        // Deduct from sender's balance
        $compte->solde -= $request->montant;
        $compte->save();

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

        // Create incoming transaction for recipient if it's a client and add to their balance
        if ($recipientType === 'client') {
            $recipientCompte = Client::find($recipientId)->compte;
            if ($recipientCompte) {
                $recipientCompte->solde += $request->montant;
                $recipientCompte->save();
                Transaction::create([
                    'compte_id' => $recipientCompte->id,
                    'type' => 'incoming_payment',
                    'montant' => $request->montant,
                    'devise' => $recipientCompte->devise,
                    'date' => now(),
                    'statut' => 'completed',
                    'reference' => 'PAY-IN-' . strtoupper(uniqid()),
                    'recipient_type' => 'self',
                    'recipient_id' => $recipientId,
                ]);
            }
        }

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
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"destinataire_telephone","montant"},
     *             @OA\Property(property="destinataire_telephone", type="string", example="7734567890"),
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

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $client = $user->client;

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé.',
            ], 404);
        }

        // if (!$client->email_verified_at) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Votre email n\'est pas confirmé.',
        //     ], 403);
        // }

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

        if ($compte->solde < $request->montant) {
            return response()->json([
                'success' => false,
                'message' => 'Solde insuffisant.',
            ], 400);
        }

        $compteDestinataire = $destinataire->compte;

        // Deduct from sender's balance and add to recipient's balance
        $compte->solde -= $request->montant;
        $compte->save();
        $compteDestinataire->solde += $request->montant;
        $compteDestinataire->save();

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

        // Create incoming transaction for recipient
        Transaction::create([
            'compte_id' => $compteDestinataire->id,
            'type' => 'incoming_transfer',
            'montant' => $request->montant,
            'devise' => $compteDestinataire->devise,
            'date' => now(),
            'statut' => 'completed',
            'reference' => 'TRF-IN-' . strtoupper(uniqid()),
            'recipient_type' => 'self',
            'recipient_id' => $compteDestinataire->client_id,
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
     *     path="/api/me",
     *     tags={"Client"},
     *     summary="Get authenticated client information, account, and transactions",
     *     security={{"passport":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations client récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="nom", type="string", example="Doe"),
     *                     @OA\Property(property="prenom", type="string", example="John"),
     *                     @OA\Property(property="telephone", type="string", example="1234567890"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="type", type="string", example="client"),
     *                     @OA\Property(property="statut", type="string", example="active"),
     *                     @OA\Property(property="is_verified", type="boolean", example=true),
     *                     @OA\Property(property="date_creation", type="string", format="date-time"),
     *                     @OA\Property(property="date_inscription", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(property="compte", type="object",
     *                     @OA\Property(property="numero_compte", type="string", example="CMPT-123456"),
     *                     @OA\Property(property="solde", type="number", format="float", example=150000),
     *                     @OA\Property(property="devise", type="string", example="XOF")
     *                 ),
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
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non autorisé"),
     *     @OA\Response(response=403, description="Email non confirmé")
     * )
     */
    public function me(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $client = $user->client;

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé.',
            ], 404);
        }

        if (!$client->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Votre email n\'est pas confirmé.',
            ], 403);
        }

        $compte = $client->compte;

        // Get recent transactions (last 10)
        $transactions = $compte ? $compte->transactions()
            ->orderBy('date', 'desc')
            ->take(10)
            ->get() : collect();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $client->id,
                    'nom' => $client->nom,
                    'prenom' => $client->prenom,
                    'telephone' => $client->telephone,
                    'email' => $client->email,
                    'type' => $user->role,
                    'statut' => 'active', // Assuming active if email verified
                    'is_verified' => !is_null($client->email_verified_at),
                    'date_creation' => $client->created_at,
                    'date_inscription' => $client->created_at,
                ],
                'compte' => $compte ? [
                    'numero_compte' => $compte->numeroCompte,
                    'solde' => $compte->solde,
                    'devise' => $compte->devise,
                ] : null,
                'transactions' => $transactions,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/compte/{id}/transactions",
     *     tags={"Compte"},
     *     summary="Get client transaction history",
     *     security={{"passport":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
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

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié.',
            ], 401);
        }

        $client = $user->client;

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé.',
            ], 404);
        }

        if (!$client->email_verified_at) {
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