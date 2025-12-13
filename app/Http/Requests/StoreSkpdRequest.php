<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkpdRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_skpd' => 'required|string|max:255|unique:skpd,nama_skpd',
            'website_url' => 'nullable|url|max:500',
            'email' => 'nullable|email|max:255',
            'kuota_bulanan' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|in:Active,Inactive',
            'server_id' => 'nullable|exists:lokasi_server,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_skpd.required' => 'Nama SKPD wajib diisi.',
            'nama_skpd.unique' => 'Nama SKPD sudah terdaftar.',
            'nama_skpd.max' => 'Nama SKPD maksimal 255 karakter.',
            'website_url.url' => 'Format URL website tidak valid.',
            'email.email' => 'Format email tidak valid.',
            'kuota_bulanan.integer' => 'Kuota bulanan harus berupa angka.',
            'kuota_bulanan.min' => 'Kuota bulanan minimal 1.',
            'server_id.exists' => 'Server yang dipilih tidak valid.',
        ];
    }
}
