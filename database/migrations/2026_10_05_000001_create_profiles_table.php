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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('username', 100)->nullable();
            $table->string('source', 50)->default('WhatsApp'); // WhatsApp, Phone, Facebook, Instagram, In-person, Other
            $table->string('email', 191)->nullable();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
            $table->string('cnic', 20)->nullable(); // 13 digits
            $table->string('phone', 20)->nullable(); // 11 digits
            $table->text('address_1')->nullable();
            $table->text('address_2')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('status', 50)->default('prospective'); // prospective, working, postponed, closed
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
