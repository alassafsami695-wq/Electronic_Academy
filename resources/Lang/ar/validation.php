<?php

return [

    /*
    |--------------------------------------------------------------------------
    | رسائل التحقق (Validation Language Lines)
    |--------------------------------------------------------------------------
    |
    | تحتوي هذه الرسائل على النصوص الافتراضية لأخطاء التحقق.
    | يمكنك تعديلها لتناسب احتياجات مشروعك.
    |
    */

    'accepted'             => 'يجب قبول :attribute.',
    'active_url'           => ':attribute ليس رابطاً صحيحاً.',
    'after'                => 'يجب أن يكون :attribute تاريخاً بعد :date.',
    'after_or_equal'       => 'يجب أن يكون :attribute تاريخاً بعد أو يساوي :date.',
    'alpha'                => 'يجب أن يحتوي :attribute على أحرف فقط.',
    'alpha_dash'           => 'يجب أن يحتوي :attribute على أحرف، أرقام، شرطات، وشرطات سفلية فقط.',
    'alpha_num'            => 'يجب أن يحتوي :attribute على أحرف وأرقام فقط.',
    'array'                => 'يجب أن يكون :attribute مصفوفة.',
    'before'               => 'يجب أن يكون :attribute تاريخاً قبل :date.',
    'before_or_equal'      => 'يجب أن يكون :attribute تاريخاً قبل أو يساوي :date.',
    'between'              => [
        'numeric' => 'يجب أن تكون قيمة :attribute بين :min و :max.',
        'file'    => 'يجب أن يكون حجم الملف :attribute بين :min و :max كيلوبايت.',
        'string'  => 'يجب أن يكون طول النص :attribute بين :min و :max حروف.',
        'array'   => 'يجب أن يحتوي :attribute على عدد عناصر بين :min و :max.',
    ],
    'boolean'              => 'يجب أن تكون قيمة :attribute صحيحة أو خاطئة.',
    'confirmed'            => 'تأكيد :attribute غير متطابق.',
    'date'                 => ':attribute ليس تاريخاً صحيحاً.',
    'date_equals'          => 'يجب أن يكون :attribute تاريخاً يساوي :date.',
    'date_format'          => ':attribute لا يطابق الشكل :format.',
    'different'            => 'يجب أن يكون :attribute مختلفاً عن :other.',
    'digits'               => 'يجب أن يحتوي :attribute على :digits أرقام.',
    'digits_between'       => 'يجب أن يحتوي :attribute على عدد أرقام بين :min و :max.',
    'email'                => 'يجب أن يكون :attribute بريداً إلكترونياً صحيحاً.',
    'exists'               => ':attribute المحدد غير صحيح.',
    'file'                 => 'يجب أن يكون :attribute ملفاً.',
    'filled'               => 'يجب أن يحتوي :attribute على قيمة.',
    'gt'                   => [
        'numeric' => 'يجب أن تكون قيمة :attribute أكبر من :value.',
        'file'    => 'يجب أن يكون حجم الملف :attribute أكبر من :value كيلوبايت.',
        'string'  => 'يجب أن يكون طول النص :attribute أكبر من :value حروف.',
        'array'   => 'يجب أن يحتوي :attribute على أكثر من :value عناصر.',
    ],
    'gte'                  => [
        'numeric' => 'يجب أن تكون قيمة :attribute أكبر من أو تساوي :value.',
        'file'    => 'يجب أن يكون حجم الملف :attribute أكبر من أو يساوي :value كيلوبايت.',
        'string'  => 'يجب أن يكون طول النص :attribute أكبر من أو يساوي :value حروف.',
        'array'   => 'يجب أن يحتوي :attribute على :value عناصر أو أكثر.',
    ],
    'image'                => 'يجب أن يكون :attribute صورة.',
    'in'                   => ':attribute المحدد غير صحيح.',
    'integer'              => 'يجب أن يكون :attribute عدداً صحيحاً.',
    'ip'                   => 'يجب أن يكون :attribute عنوان IP صحيحاً.',
    'json'                 => 'يجب أن يكون :attribute نص JSON صحيحاً.',
    'max'                  => [
        'numeric' => 'يجب ألا تكون قيمة :attribute أكبر من :max.',
        'file'    => 'يجب ألا يكون حجم الملف :attribute أكبر من :max كيلوبايت.',
        'string'  => 'يجب ألا يكون طول النص :attribute أكبر من :max حروف.',
        'array'   => 'يجب ألا يحتوي :attribute على أكثر من :max عناصر.',
    ],
    'min'                  => [
        'numeric' => 'يجب أن تكون قيمة :attribute على الأقل :min.',
        'file'    => 'يجب أن يكون حجم الملف :attribute على الأقل :min كيلوبايت.',
        'string'  => 'يجب أن يكون طول النص :attribute على الأقل :min حروف.',
        'array'   => 'يجب أن يحتوي :attribute على الأقل :min عناصر.',
    ],
    'not_in'               => ':attribute المحدد غير صحيح.',
    'numeric'              => 'يجب أن تكون قيمة :attribute رقماً.',
    'present'              => 'يجب تقديم :attribute.',
    'regex'                => 'صيغة :attribute غير صحيحة.',
    'required'             => 'حقل :attribute مطلوب.',
    'required_if'          => 'حقل :attribute مطلوب عندما يكون :other يساوي :value.',
    'same'                 => 'يجب أن يتطابق :attribute مع :other.',
    'size'                 => [
        'numeric' => 'يجب أن تكون قيمة :attribute :size.',
        'file'    => 'يجب أن يكون حجم الملف :attribute :size كيلوبايت.',
        'string'  => 'يجب أن يكون طول النص :attribute :size حروف.',
        'array'   => 'يجب أن يحتوي :attribute على :size عناصر.',
    ],
    'string'               => 'يجب أن يكون :attribute نصاً.',
    'unique'               => ':attribute مستخدم بالفعل.',
    'url'                  => 'صيغة الرابط :attribute غير صحيحة.',
    'uuid'                 => 'يجب أن يكون :attribute UUID صحيحاً.',

    /*
    |--------------------------------------------------------------------------
    | رسائل مخصصة (Custom Validation Language Lines)
    |--------------------------------------------------------------------------
    |
    | يمكنك تحديد رسائل مخصصة لحقول معينة باستخدام صيغة
    | "attribute.rule" لتسمية الرسالة.
    |
    */

    'custom' => [
        'email' => [
            'required' => 'البريد الإلكتروني مطلوب.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | أسماء السمات (Custom Attributes)
    |--------------------------------------------------------------------------
    |
    | يمكنك استبدال أسماء الحقول الافتراضية بأسماء أكثر وضوحاً للمستخدم.
    |
    */

    'attributes' => [
        'email'    => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'title'    => 'العنوان',
        'body'     => 'النص',
    ],

];
