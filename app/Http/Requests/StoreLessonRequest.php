<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'order' => 'required|integer|min:1',
            'video' => 'sometimes|file|mimetypes:video/mp4,video/avi,video/mpeg|max:204800',
            'content' => 'nullable|string',
        ];
    }
}
