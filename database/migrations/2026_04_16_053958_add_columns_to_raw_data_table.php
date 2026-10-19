<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_data', function (Blueprint $table) {
            $table->string('case_id_manual')->nullable();
            $table->string('company_name')->nullable();
            $table->date('received_date')->nullable();
            $table->string('warranty_status')->nullable();
            $table->string('serial_no')->nullable();
            $table->string('product_no')->nullable();
            $table->string('product_name')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('mobile_no')->nullable();
            $table->date('start_repair_date')->nullable();
            $table->string('tat_case')->nullable();
            $table->date('finish_date')->nullable();
            $table->date('closed_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('raw_data', function (Blueprint $table) {
            $table->dropColumn([
                'case_id_manual',
                'company_name',
                'received_date',
                'warranty_status',
                'serial_no',
                'product_no',
                'product_name',
                'phone_no',
                'mobile_no',
                'start_repair_date',
                'tat_case',
                'finish_date',
                'closed_date',
            ]);
        });
    }
};