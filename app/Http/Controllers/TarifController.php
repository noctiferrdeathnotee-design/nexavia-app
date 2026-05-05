<?php

namespace App\Http\Controllers;

use App\Models\Kota;
use App\Services\TarifService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TarifController extends Controller
{
    public function index(): Response
    {
        $kotaList = Kota::query()
            ->where('status', 'aktif')
            ->select('id', 'nama_kota')
            ->orderBy('nama_kota')
            ->get();

        return Inertia::render('Tarif/Index', [
            'title' => 'Cek Tarif',
            'kotaList' => $kotaList,
        ]);
    }

    public function cek(Request $request, TarifService $tarifService): JsonResponse
    {
        $validated = $request->validate([
            'kota_asal_id' => ['required', 'integer', 'exists:kota,id'],
            'kota_tujuan_id' => ['required', 'integer', 'exists:kota,id'],
            'berat_kg' => ['required', 'numeric', 'min:0.1'],
        ], [
            'kota_asal_id.required' => 'Kota asal wajib dipilih.',
            'kota_asal_id.exists' => 'Kota asal tidak valid.',
            'kota_tujuan_id.required' => 'Kota tujuan wajib dipilih.',
            'kota_tujuan_id.exists' => 'Kota tujuan tidak valid.',
            'berat_kg.required' => 'Berat wajib diisi.',
            'berat_kg.numeric' => 'Berat harus berupa angka.',
            'berat_kg.min' => 'Berat minimal 0.1 kg.',
        ]);

        $tarif = $tarifService->hitung(
            (int) $validated['kota_asal_id'],
            (int) $validated['kota_tujuan_id'],
            (float) $validated['berat_kg']
        );

        return response()->json([
            'tarif' => $tarif,
            'error' => empty($tarif)
                ? 'Tarif untuk kombinasi kota ini belum tersedia.'
                : null,
        ]);
    }
}
