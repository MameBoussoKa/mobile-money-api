<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarchandRequest extends FormRequest
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
            'user_id' => 'required|uuid|exists:users,id',
            'nom' => 'required|string|max:255',
            'codeMarchand' => 'required|string|unique:marchands,codeMarchand,' . $this->route('marchand'),
            'categorie' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string',
            'qrCode' => 'nullable|string',
        ];
    }
}
