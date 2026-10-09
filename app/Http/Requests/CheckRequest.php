<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi permintaan pengecekan penipuan (POST /api/check).
 */
class CheckRequest extends FormRequest
{
    /**
     * Endpoint publik, semua pengguna boleh memeriksa.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:phone,bank_account,ewallet,url,email,message,other',
            'value' => 'required|string|max:2000',
        ];
    }
}
