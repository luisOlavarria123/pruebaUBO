<?php

namespace App\Http\Requests\Categoria;

use Illuminate\Foundation\Http\FormRequest;

class DestroyCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRevisor() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
