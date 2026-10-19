<?php

namespace App\Services;

/*
|--------------------------------------------------------------------------
| PartLaborMapping
|--------------------------------------------------------------------------
| ★★★ BARU: penampung sementara hasil hitung Labor per Case ID dari sheet
| "Parts", dibaca oleh ApoImport saat memproses sheet "Transaction Report".
| Sama persis polanya dengan CompanyMapping yang sudah dipakai untuk MPR
| (MprExportImport mengisi $mapping duluan, MprImport membacanya belakangan).
|--------------------------------------------------------------------------
*/

class PartLaborMapping
{
    /**
     * @var array<string, array{labor: ?float, part_no: ?string, part_name: ?string, category: ?string}>
     */
    public static array $mapping = [];
}