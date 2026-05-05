<?php

namespace App\Services;

use App\Models\Pengiriman;

class ResiService
{
    public function generate(): string
    {
        $prefix = 'SS-' . now()->format('ymd') . '-';
        $count = Pengiriman::whereDate('created_at', today())->count() + 1;

        do {
            $nomor = $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $exists = Pengiriman::where('nomor_resi', $nomor)->exists();
            $count++;
        } while ($exists);

        return $nomor;
    }
}
