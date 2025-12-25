<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): bool|array
    {
        $user = auth()->user();

        // القواعد المشتركة للجميع (طالب، مدرس، أدمن)
        $rules = [
            'name'         => 'sometimes|string|max:255',
            'photo'        => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address'      => 'nullable|string|max:500',
            'phone_number' => 'nullable|string|max:20',
            'birth_date'   => 'nullable|date', // التحقق من الحقل الجديد
        ];

        // قواعد إضافية تظهر فقط إذا كان المستخدم مدرساً
        if ($user && $user->role === 'teacher') {
            $rules = array_merge($rules, [
                'facebook_url'  => 'nullable|url',
                'linkedin_url'  => 'nullable|url',
                'instagram_url' => 'nullable|url',
                'youtube_url'   => 'nullable|url',
                'github_url'    => 'nullable|url',
            ]);
        }

        return $rules;
    }
}