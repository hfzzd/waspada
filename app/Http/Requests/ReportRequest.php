<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi laporan penipuan dari komunitas (POST /api/reports).
 */
class ReportRequest extends FormRequest
{
    /**
     * Endpoint publik, semua pengguna boleh melapor.
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
            'category' => 'required|in:phising,investasi_bodong,love_scam,pinjol_ilegal,undangan_apk,hadiah_palsu,jual_beli,lainnya',
            'description' => 'required|string|min:10|max:5000',
            'reporter_name' => 'nullable|string|max:255',
            'reporter_contact' => 'nullable|string|max:255',
            'evidence_url' => 'nullable|url|max:2000',
        ];
    }
}
