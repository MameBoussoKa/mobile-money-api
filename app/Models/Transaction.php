<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'compte_id',
        'type',
        'montant',
        'devise',
        'date',
        'statut',
        'reference',
        'marchand_id',
        'recipient_type',
        'recipient_id',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date' => 'datetime',
    ];

    protected $attributes = [
        'statut' => 'pending',
    ];

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }

    public function marchand()
    {
        return $this->belongsTo(Marchand::class);
    }

    public function recipientClient()
    {
        return $this->belongsTo(Client::class, 'recipient_id');
    }

    public function recipientMarchand()
    {
        return $this->belongsTo(Marchand::class, 'recipient_id');
    }

    public function validerTransaction()
    {
        $this->statut = 'completed';
        $this->save();
    }

    public function annulerTransaction()
    {
        $this->statut = 'cancelled';
        $this->save();
    }
}
