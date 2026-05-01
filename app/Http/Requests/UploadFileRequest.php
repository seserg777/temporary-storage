<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class UploadFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! ($value instanceof UploadedFile)) {
                        return;
                    }

                    $ext = strtolower($value->getClientOriginalExtension());

                    if (! in_array($ext, ['pdf', 'docx'], true)) {
                        $fail('The :attribute must have a .pdf or .docx extension.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.mimetypes' => 'Only PDF and DOCX files are allowed.',
            'file.max' => 'The file must not exceed 10 MB.',
        ];
    }
}
