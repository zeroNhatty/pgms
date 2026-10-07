<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePowerNodesRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nodes' => ['required', 'array', 'min:1'],
            'nodes.*.clientId' => ['required', 'string'],
            'nodes.*.longitude' => ['required', 'numeric', 'between:-180,180'],
            'nodes.*.latitude' => ['required', 'numeric', 'between:-90,90'],

            'relations' => ['sometimes', 'array'],
            'relations.*.from' => ['required', 'string'],
            'relations.*.to' => ['required', 'string', 'different:relations.*.from'],
        ];
    }
}
