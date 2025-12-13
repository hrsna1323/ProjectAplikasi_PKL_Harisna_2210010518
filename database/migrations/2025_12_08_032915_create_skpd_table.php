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
        Schema::create('skpd', function (Blueprint $table) {
            $table->id();
            $table->string('nama_skpd');
            $table->string('website_url')->nullable();
            $table->string('email')->nullable();
            $table->integer('kuota_bulanan')->default(3);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->unsignedBigInteger('server_id')->nullable();
            $table->timestamps();

            $table->foreign('server_id')->references('id')->on('lokasi_server')->onDelete('set null');
        });

        // Add foreign key to users table after skpd table is created
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('skpd_id')->references('id')->on('skpd')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['skpd_id']);
        });
        Schema::dropIfExists('skpd');
    }
};
