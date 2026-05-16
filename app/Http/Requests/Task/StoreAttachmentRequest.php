<?php


namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    private const ALLOWED_MIMES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain', 'text/csv',
        'application/zip',
        'application/x-zip-compressed',
    ];

    private const MAX_SIZE_KB = 20480; // 20 MB

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:' . self::MAX_SIZE_KB,
                'mimetypes:' . implode(',', self::ALLOWED_MIMES),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimetypes' => 'The file type is not allowed. Allowed types: images, PDF, Office documents, text, zip.',
            'file.max' => 'The file may not be larger than 20 MB.',
        ];
    }
}
