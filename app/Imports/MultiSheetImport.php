<?php

namespace App\Imports;
use App\Imports\PembayaranImport;
use App\Models\ReminderInvoice;
use App\Imports\MprImport;
use App\Imports\MprExportImport;

use Exception;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetImport implements WithMultipleSheets
{
    protected $jenisUpload;
    protected $kodeUpload;
    protected $apoCaseOverrides; // [DITAMBAHKAN]

    // [DITAMBAHKAN] diteruskan dari ImportController::commitApoUpload()
    // ke ApoImport, untuk keterangan manual per Case ID dan aturan
    // "RR (repair ulang) gak dapat Labor".
    protected $apoKeteranganOverrides;
    protected $rrRepeatCaseIds;

    public function __construct(
        $jenisUpload,
        $kodeUpload,
        $apoCaseOverrides = [],
        $apoKeteranganOverrides = [], // [DITAMBAHKAN]
        $rrRepeatCaseIds = [] // [DITAMBAHKAN]
    ) {
        $this->jenisUpload = $jenisUpload;
        $this->kodeUpload = $kodeUpload;
        $this->apoCaseOverrides = $apoCaseOverrides; // [DITAMBAHKAN]
        $this->apoKeteranganOverrides = $apoKeteranganOverrides; // [DITAMBAHKAN]
        $this->rrRepeatCaseIds = $rrRepeatCaseIds; // [DITAMBAHKAN]
    }

    public function sheets(): array
    {
        switch ($this->jenisUpload) {

            case 'W001-WIP':
                return [
                    'Parts' => new WipImport($this->kodeUpload),
                ];
            case 'W002-TAT14D (Pending case 14 days)':
                return [
                    'Export' => new PendingCaseTat14dImport(
                        $this->kodeUpload
                    ),
                ];

            case 'W003-TAT5D (Pending case 5 days)':
                return [
                    'Export' => new PendingCaseTat5dImport(
                        $this->kodeUpload
                    ),
                ];
            
            case 'W004-KCI':
                return [
                    'Export' => new KciImport(
                        $this->kodeUpload
                    ),
                ];

            case 'W005-FR (Finish Repair)':
                return [
                    'Export' => new FinishRepairImport(
                        $this->kodeUpload
                    ),
                ];
            // ==========================
            // MPR
            // ==========================
            case 'M001-MPR':
                return [

                    'Export' => new MprExportImport(),

                    'MPR' => new MprImport(
                        $this->kodeUpload
                    ),

                ];

             case 'M002-Reminder Invoice':
                return [
                    'Sheet1' => new ReminderInvoiceImport(
                        $this->kodeUpload
                    ),
                ];
            
            case 'M003-Pembayaran':
                return [
                    'Sheet1' => new PembayaranImport(
                        $this->kodeUpload
                    ),
                ];
            case 'D001-KLA':
                return [
                    'Form Responses 1' => new KlaImport(
                        $this->kodeUpload
                    ),
                ];

            case 'M004-PO Labor':
                return [
                    // [DIUBAH] Diproses DULUAN — isi PartLaborMapping::$mapping. Sekarang
                    // terima $apoCaseOverrides (pilihan part per Case ID dari popup review
                    // di Upload Sheet) — kalau ada, dipakai persis itu, bukan hitung ulang
                    // otomatis (Defective + kategori terbesar).
                    'Parts' => new ApoPartsImport($this->apoCaseOverrides),

                    // [DIUBAH] Sekarang terima juga $apoKeteranganOverrides (keterangan
                    // manual per Case ID dari popup review) dan $rrRepeatCaseIds (Case ID
                    // yang merupakan RR/repair ulang -> Labor dipaksa null).
                    'Transaction Report' => new ApoImport(
                        $this->kodeUpload,
                        $this->apoKeteranganOverrides, // [DITAMBAHKAN]
                        $this->rrRepeatCaseIds // [DITAMBAHKAN]
                    ),
                ];


           

            default:
                throw new Exception(
                    'Jenis monitoring tidak dikenali : ' .
                    $this->jenisUpload
                );
        }
    }
}