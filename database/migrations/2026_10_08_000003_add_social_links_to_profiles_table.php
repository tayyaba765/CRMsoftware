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
            $table->string('facebook_link', 255)->nullable()->after('age_group');
            $table->string('instagram_link', 255)->nullable()->after('facebook_link');
            $table->string('linkedin_link', 255)->nullable()->after('instagram_link');
            $table->string('threads_link', 255)->nullable()->after('linkedin_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_link',
                'instagram_link',
                'linkedin_link',
                'threads_link',
            ]);
        });
    }
};
