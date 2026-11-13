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
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('job_type', 50)->nullable()->after('status'); // private, government, unemployed, public
            $table->decimal('income', 12, 2)->nullable()->after('job_type'); // income in $ float
            $table->string('education', 50)->nullable()->after('income'); // highschool, bs, masters, etc.
            $table->string('age_group', 50)->nullable()->after('education'); // teen, adult, olderadult, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['job_type', 'income', 'education', 'age_group']);
        });
    }
};
