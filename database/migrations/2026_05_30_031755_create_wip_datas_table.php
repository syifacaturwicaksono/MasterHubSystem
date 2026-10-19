<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wip_datas', function (Blueprint $table) {
            $table->id();

            $table->string('kode_upload')->nullable();
            $table->string('jenis_monitoring')->default('WIP');

            $table->string('case_id_manual')->nullable();
            $table->string('company_name')->nullable();

            $table->dateTime('finish_date')->nullable();

            $table->string('case_status')->nullable();
            $table->string('hp_part_no')->nullable();
            $table->string('so_no')->nullable();
            $table->string('awb_no_part_return')->nullable();

            $table->dateTime('part_in_date')->nullable();

            $table->decimal('aging', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wip_datas');
    }
};