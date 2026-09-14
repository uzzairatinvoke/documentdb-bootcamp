<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Create: admin + manager. Update: admin only.
        if ($this->isMethod('post')) {
            return $this->user()->hasAnyRole(['admin', 'manager']);
        }

        return $this->user()->hasRole('admin');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|min:6',
            'description' => 'required|min:6',
            'document' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'file',
                'mimes:pdf,png,jpeg,doc,docx,txt',
                'max:10240',
            ],
        ];
    }
}
