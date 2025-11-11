<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    use HasFactory;

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
        'solde' => 'decimal:2',
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

    public function crediter($montant)
    {
        $this->solde += $montant;
        $this->dateDerniereMaj = now();
        $this->save();
    }

    public function debiter($montant)
    {
        if ($this->solde >= $montant) {
            $this->solde -= $montant;
            $this->dateDerniereMaj = now();
            $this->save();
            return true;
        }
        return false;
    }

    public function afficherSolde()
    {
        return $this->solde;
    }
}
