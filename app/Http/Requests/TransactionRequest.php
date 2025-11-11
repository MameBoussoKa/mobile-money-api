<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'compte_id' => 'required|uuid|exists:comptes,id',
            'type' => 'required|in:debit,credit,transfer',
            'montant' => 'required|numeric|min:0.01',
            'devise' => 'string|size:3',
            'date' => 'required|date',
            'statut' => 'in:pending,completed,cancelled',
            'reference' => 'required|string|unique:transactions,reference,' . $this->route('transaction'),
            'marchand_id' => 'nullable|uuid|exists:marchands,id',
        ];
    }
}
