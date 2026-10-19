<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {

            $table->id();

            // Company
            $table->string('kode_company', 20);

            // Template yang digunakan
            $table->string('template_name')->nullable();

            // Subject email
            $table->string('subject')->nullable();

            // Email tujuan
            $table->text('recipient');

            // Jumlah case yang terkirim
            $table->integer('total_case')->default(0);

            // Filter aging yang digunakan
            $table->string('aging_filter')->nullable();

            // Waktu kirim email
            $table->timestamp('sent_at');

            $table->timestamps();

            $table->index('kode_company');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};