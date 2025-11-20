<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'client_id',
        'numeroCompte',
        'solde',
        'devise',
        'dateDerniereMaj',
    ];

    protected $casts = [
        'dateDerniereMaj' => 'datetime',
    ];


    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getSoldeAttribute()
    {
        $incomingTypes = ['incoming_payment', 'incoming_transfer', 'deposit'];
        $outgoingTypes = ['payment', 'transfer'];

        $incoming = $this->transactions()->whereIn('type', $incomingTypes)->sum('montant');
        $outgoing = $this->transactions()->whereIn('type', $outgoingTypes)->sum('montant');

        return $incoming - $outgoing;
    }

}
