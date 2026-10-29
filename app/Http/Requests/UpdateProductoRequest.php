<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas para ACTUALIZAR (PUT/PATCH /api/productos/{id}).
     * "sometimes" = solo se valida si el campo viene en la petición (permite PATCH parcial).
     */
    public function rules(): array
    {
        $id = $this->route('producto')?->id;

        return [
            'sku' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('productos', 'sku')->ignore($id)],
            'nombre' => ['sometimes', 'required', 'string', 'max:120'],
            'descripcion' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'categoria' => ['sometimes', 'required', 'string', 'max:60'],
            'precio' => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999999.99'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return (new StoreProductoRequest)->messages();
    }
}
