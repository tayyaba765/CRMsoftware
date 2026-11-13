<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {

            $table->foreignId('module_id')
                ->nullable()
                ->after('guard_name')
                ->constrained('modules')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {

            $table->dropForeign(['module_id']);
            $table->dropColumn('module_id');

        });
    }
};