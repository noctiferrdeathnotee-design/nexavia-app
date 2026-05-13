<?php

namespace App\Http\Controllers;

use App\Models\Pengiriman;
use Illuminate\View\View;
use Picqer\Barcode\BarcodeGeneratorSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\Common\EccLevel;
use Symfony\Component\HttpFoundation\Response;
use TCPDF;

class ResiController extends Controller
{
    private function getQrCode($text)
    {
        $options = new QROptions([
            'outputType'  => QRMarkupSVG::class,
            'eccLevel'    => EccLevel::L,
            'scale'       => 4,
            'imageBase64' => true,
        ]);
        return (new QRCode($options))->render($text);
    }

    private function getBarcode($resi)
    {
        $generator = new BarcodeGeneratorSVG();
        $barcode = $generator->getBarcode($resi, $generator::TYPE_CODE_128, 2, 48);
        return 'data:image/svg+xml;base64,' . base64_encode($barcode);
    }

    public function print(Pengiriman $pengiriman): View
    {
        $pengiriman->load([
            'pengirimKota:id,nama_kota,provinsi,kode_pos',
            'penerimaKota:id,nama_kota,provinsi,kode_pos',
            'barang',
            'admin:id,nama,email',
        ]);

        $trackingUrl = route('tracking.show', $pengiriman->nomor_resi);

        return view('resi.print', [
            'pengiriman' => $pengiriman,
            'trackingUrl' => $trackingUrl,
            'qrCode' => $this->getQrCode($pengiriman->nomor_resi),
            'barcode' => $this->getBarcode($pengiriman->nomor_resi),
        ]);
    }

    public function pdf(Pengiriman $pengiriman): Response
    {
        $pengiriman->load([
            'pengirimKota:id,nama_kota,provinsi,kode_pos',
            'penerimaKota:id,nama_kota,provinsi,kode_pos',
            'barang',
            'admin:id,nama,email',
        ]);

        $trackingUrl = route('tracking.show', $pengiriman->nomor_resi);

        $html = view('resi.pdf', [
            'pengiriman' => $pengiriman,
            'trackingUrl' => $trackingUrl,
            'qrCode' => $this->getQrCode($pengiriman->nomor_resi),
            'barcode' => $this->getBarcode($pengiriman->nomor_resi),
        ])->render();

        $pdf = new TCPDF('L', 'mm', 'A5', true, 'UTF-8', false);
        $pdf->SetTitle('Resi - ' . $pengiriman->nomor_resi);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(8, 8, 8);
        $pdf->SetAutoPageBreak(TRUE, 8);
        $pdf->AddPage();
        
        $pdf->writeHTML($html, true, false, true, false, '');

        return response($pdf->Output('resi-' . $pengiriman->nomor_resi . '.pdf', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="resi-' . $pengiriman->nomor_resi . '.pdf"'
        ]);
    }
}
