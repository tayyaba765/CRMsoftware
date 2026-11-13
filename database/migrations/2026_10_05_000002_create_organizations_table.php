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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('size', 50)->default('2-15'); // 2-15, 15-50, 50-100, 100-500, 500+
            $table->string('website_domain', 191)->nullable();
            $table->string('logo')->nullable(); // upload path
            $table->string('organization_type', 100)->nullable(); // IT, Healthcare, Education, Manufacturing, etc.
            $table->decimal('revenue', 15, 2)->nullable(); // Annual revenue in USD
            $table->string('ownership_type', 50)->default('private'); // public, private, non-profit, government
            $table->string('phone', 30)->nullable();
            $table->string('phone_2', 30)->nullable();
            $table->text('address')->nullable();
            $table->text('address_2')->nullable();
            $table->string('tier', 50)->default('Tier 1'); // Tier 1, Tier 2, Tier 3, Tier 4
            $table->string('focal_person', 150)->nullable(); // Name / contact of focal person
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
