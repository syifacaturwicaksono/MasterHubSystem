<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('raw_data', function (Blueprint $table) {
           $table->string('case_id')->nullable();
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_data');
    }
};
