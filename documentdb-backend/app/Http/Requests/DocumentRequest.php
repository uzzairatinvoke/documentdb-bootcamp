<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization dipindah ke Gate/Policy dalam DocumentsController
        // supaya demo Gate vs Policy lebih jelas. Form Request fokus pada validation.
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|min:6',
            'description' => 'required|min:6',
            'category_id' => 'nullable|exists:categories,id',
            'document' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'file',
                'mimes:pdf,png,jpeg,doc,docx,txt',
                'max:10240',
            ],
        ];
    }
}
