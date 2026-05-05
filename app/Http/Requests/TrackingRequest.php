<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_baru' => ['required', 'in:diproses,dalam_perjalanan,tiba_di_kota_tujuan,sedang_diantar,terkirim,gagal,dibatalkan'],
            'lokasi' => ['required', 'string', 'max:200'],
            'keterangan' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status_baru.required' => 'Status baru wajib dipilih.',
            'status_baru.in' => 'Status baru tidak valid.',
            'lokasi.required' => 'Lokasi wajib diisi.',
            'keterangan.required' => 'Keterangan wajib diisi.',
        ];
    }
}
