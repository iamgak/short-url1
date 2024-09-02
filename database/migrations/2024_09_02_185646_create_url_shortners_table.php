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
        Schema::create('url_shortners', function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(false);
            $table->unsignedInteger('traffic')->default(0);
            $table->string('long_url', 2048); // assuming a reasonable max length for URLs
            $table->string('hash_value', 255)->nullable(); // assuming a reasonable max length for hash values
            $table->timestamps(); // this will create both created_at and updated_at timestamps
            
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_shortners');
    }
};
