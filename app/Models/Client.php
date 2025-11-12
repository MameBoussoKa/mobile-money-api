<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'telephone',
        'email',
        'confirmation_code',
        'email_verified_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function compte()
    {
        return $this->hasOne(Compte::class);
    }

    public function consulterSolde()
    {
        return $this->compte ? $this->compte->afficherSolde() : 0;
    }

    public function effectuerTransfert($montant, $destinataire)
    {
        // Logic for transfer
        return true;
    }

    public function payerMarchand($montant, $marchand)
    {
        // Logic for payment
        return true;
    }
}
