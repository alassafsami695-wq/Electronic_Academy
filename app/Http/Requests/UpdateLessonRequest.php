<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // اسمح للجميع أو عدل للمناسب
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'order' => 'sometimes|integer|min:1',
            'content' => 'sometimes|string',
            'video_url' => 'sometimes|file|mimetypes:video/mp4,video/avi,video/mpeg|max:204800',
        ];
    }
}
