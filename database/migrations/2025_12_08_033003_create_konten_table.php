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
        Schema::create('konten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skpd_id')->constrained('skpd')->onDelete('cascade');
            $table->foreignId('publisher_id')->constrained('users')->onDelete('cascade');
            $table->string('judul', 255);
            $table->text('deskripsi');
            $table->foreignId('kategori_id')->constrained('kategori_konten')->onDelete('cascade');
            $table->string('url_publikasi', 500);
            $table->date('tanggal_publikasi');
            $table->enum('status', ['Draft', 'Pending', 'Approved', 'Rejected', 'Published'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konten');
    }
};
