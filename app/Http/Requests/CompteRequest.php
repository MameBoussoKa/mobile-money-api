<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompteRequest extends FormRequest
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
            'client_id' => 'required|uuid|exists:clients,id',
            'numeroCompte' => 'required|string|unique:comptes,numeroCompte,' . $this->route('compte'),
            'solde' => 'numeric|min:0',
            'devise' => 'string|size:3',
            'dateDerniereMaj' => 'nullable|date',
        ];
    }
}
