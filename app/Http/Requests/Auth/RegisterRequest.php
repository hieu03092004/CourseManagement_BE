<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fullName' => ['required','string','max:50'],
            'userName' => ['required','string','max:50'],
            'email' => ['required','string','email:rfc,dns','max:50','unique:user,email'],
            'phone' => ['required','string','max:10'],
            // nếu FE chưa đổi, dùng passwordHash; nếu đã đổi, dùng password
            'passwordHash' => ['required','string','min:6'],
        ];
    }
}