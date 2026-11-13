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
        Schema::create('pdf_scraped_records', function (Blueprint $table) {
            $table->id();
            $table->string('source_url', 500)->nullable();
            $table->string('source_name', 150)->nullable()->index();
            $table->string('business_name', 255)->nullable()->index(); // FIRM NAME
            $table->string('category', 255)->nullable()->index();      // CATEGORY
            $table->text('address')->nullable();                       // ADDRESS
            $table->string('contact_person', 255)->nullable()->index(); // NAME
            $table->string('phone', 100)->nullable()->index();         // MOBILE
            $table->string('phone_2', 100)->nullable();                // SECONDARY MOBILE
            $table->string('email', 191)->nullable()->index();
            $table->string('website_domain', 255)->nullable();
            $table->string('status', 50)->default('new')->index();
            $table->json('raw_data')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_scraped_records');
    }
};
