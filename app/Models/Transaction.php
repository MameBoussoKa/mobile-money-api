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
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date' => 'datetime',
    ];

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }

    public function marchand()
    {
        return $this->belongsTo(Marchand::class);
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
