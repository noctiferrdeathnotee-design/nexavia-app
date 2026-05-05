<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PengirimanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pengirim_nama' => ['required', 'string', 'max:100'],
            'pengirim_hp' => ['required', 'string', 'regex:/^(08|628|62)\d{8,11}$/'],
            'pengirim_alamat' => ['required', 'string', 'min:15', 'max:500'],
            'pengirim_kota_id' => ['required', 'integer', 'exists:kota,id'],

            'penerima_nama' => ['required', 'string', 'max:100'],
            'penerima_hp' => ['required', 'string', 'regex:/^(08|628|62)\d{8,11}$/'],
            'penerima_alamat' => ['required', 'string', 'min:15', 'max:500'],
            'penerima_kota_id' => ['required', 'integer', 'exists:kota,id'],

            'barang' => ['required', 'array', 'min:1'],
            'barang.*.nama_barang' => ['required', 'string', 'max:100'],
            'barang.*.berat_asli_kg' => ['required', 'numeric', 'min:0.1'],
            'barang.*.panjang_cm' => ['required', 'numeric', 'min:0'],
            'barang.*.lebar_cm' => ['required', 'numeric', 'min:0'],
            'barang.*.tinggi_cm' => ['required', 'numeric', 'min:0'],
            'barang.*.keterangan' => ['nullable', 'string', 'max:255'],

            'jenis_layanan' => ['required', 'in:ekonomis,reguler,express,same_day'],
            'biaya_asuransi' => ['nullable', 'numeric', 'min:0'],
            'biaya_tambahan' => ['nullable', 'numeric', 'min:0'],
            'metode_pembayaran' => ['required', 'in:dibayar_pengirim,dibayar_penerima,cod'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'pengirim_hp.regex' => 'Nomor HP pengirim tidak valid.',
            'penerima_hp.regex' => 'Nomor HP penerima tidak valid.',
            'barang.required' => 'Minimal harus ada 1 barang.',
            'barang.*.nama_barang.required' => 'Nama barang wajib diisi.',
            'barang.*.berat_asli_kg.required' => 'Berat asli wajib diisi.',
            'barang.*.berat_asli_kg.min' => 'Berat asli minimal 0.1 kg.',
            'jenis_layanan.required' => 'Jenis layanan wajib dipilih.',
            'metode_pembayaran.required' => 'Metode pembayaran wajib dipilih.',
        ];
    }
}
