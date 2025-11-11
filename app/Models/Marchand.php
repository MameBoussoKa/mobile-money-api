<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marchand extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'nom',
        'codeMarchand',
        'categorie',
        'telephone',
        'adresse',
        'qrCode',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function genererQRCode()
    {
        // Logic to generate QR code
        $this->qrCode = 'QR-' . $this->codeMarchand;
        $this->save();
    }

    public function recevoirPaiement($montant)
    {
        // Logic to receive payment
        return true;
    }
}
