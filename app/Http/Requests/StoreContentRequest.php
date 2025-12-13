<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Publisher');
    }

    public function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'kategori_id' => 'required|exists:kategori_konten,id',
            'url_publikasi' => 'required|url|max:500',
            'tanggal_publikasi' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul konten wajib diisi.',
            'judul.max' => 'Judul konten maksimal 255 karakter.',
            'deskripsi.required' => 'Deskripsi konten wajib diisi.',
            'kategori_id.required' => 'Kategori konten wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',
            'url_publikasi.required' => 'URL publikasi wajib diisi.',
            'url_publikasi.url' => 'Format URL publikasi tidak valid.',
            'url_publikasi.max' => 'URL publikasi maksimal 500 karakter.',
            'tanggal_publikasi.required' => 'Tanggal publikasi wajib diisi.',
            'tanggal_publikasi.date' => 'Format tanggal publikasi tidak valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('judul')) {
            $this->merge(['judul' => strip_tags($this->judul)]);
        }
        if ($this->has('deskripsi')) {
            $this->merge(['deskripsi' => strip_tags($this->deskripsi)]);
        }
    }
}
