<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kode (model, import, email) memakai kolom `jatuh_tempo` di tabel `reminder`,
 * sedangkan database lama memakai nama `status` (tipe date).
 * Migration ini aman dijalankan berulang: hanya mengubah bila perlu.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reminder') || Schema::hasColumn('reminder', 'jatuh_tempo')) {
            return;
        }

        if (Schema::hasColumn('reminder', 'status')) {
            Schema::table('reminder', function (Blueprint $table) {
                $table->renameColumn('status', 'jatuh_tempo');
            });
        } else {
            Schema::table('reminder', function (Blueprint $table) {
                $table->date('jatuh_tempo')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Sengaja kosong: tidak mengembalikan nama kolom lama.
    }
};
