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
    Schema::table('roles', function (Blueprint $table) {
        $table->foreignId('module_id')
            ->nullable()
            ->after('guard_name')
            ->constrained('modules')
            ->nullOnDelete();

        $table->foreignId('parent_role_id')
            ->nullable()
            ->after('module_id')
            ->constrained('roles')
            ->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('roles', function (Blueprint $table) {
        $table->dropConstrainedForeignId('module_id');
        $table->dropConstrainedForeignId('parent_role_id');
    });
}



};
