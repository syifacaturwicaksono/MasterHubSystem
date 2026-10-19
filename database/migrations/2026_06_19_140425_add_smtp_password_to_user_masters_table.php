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
        Schema::table('user_masters', function (Blueprint $table) {
            if (!Schema::hasColumn('user_masters', 'smtp_password')) {
                $table->string('smtp_password')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_masters', function (Blueprint $table) {
            if (Schema::hasColumn('user_masters', 'smtp_password')) {
                $table->dropColumn('smtp_password');
            }
        });
    }
};
