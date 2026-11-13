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
        Schema::create('scraped_records', function (Blueprint $table) {
            $table->id();
            $table->string('source_url', 500)->nullable();
            $table->string('source_name', 150)->nullable()->index();
            $table->string('business_name', 255)->nullable()->index();
            $table->string('contact_person', 255)->nullable()->index();
            $table->string('phone', 100)->nullable()->index();
            $table->string('phone_2', 100)->nullable();
            $table->string('email', 191)->nullable()->index();
            $table->string('category', 255)->nullable()->index();
            $table->text('address')->nullable();
            $table->string('website_domain', 255)->nullable();
            $table->string('status', 50)->default('new')->index();
            $table->json('raw_data')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('scraper_logs', function (Blueprint $table) {
            $table->id();
            $table->string('url', 500);
            $table->string('source_name', 150)->nullable();
            $table->string('status', 50)->default('pending')->index();
            $table->unsignedInteger('records_found')->default(0);
            $table->unsignedInteger('records_imported')->default(0);
            $table->text('error_message')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraper_logs');
        Schema::dropIfExists('scraped_records');
    }
};
