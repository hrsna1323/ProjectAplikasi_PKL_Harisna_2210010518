<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table is optional - for maintaining compatibility with old system
     * that tracked infrastructure details
     */
    public function up(): void
    {
        Schema::create('subdomain', function (Blueprint $table) {
            $table->id('subdomain_id');
            $table->unsignedBigInteger('skpd_id')->nullable();
            $table->unsignedBigInteger('server_id')->nullable();
            $table->string('nama_web', 255);
            $table->string('subdomain', 255)->unique();
            $table->enum('status_dns', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->enum('status_ssl', ['Aktif', 'Tidak Aktif', 'Expired'])->default('Tidak Aktif');
            $table->date('tanggal_expired_ssl')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('skpd_id')
                  ->references('id')
                  ->on('skpd')
                  ->onDelete('set null');

            $table->foreign('server_id')
                  ->references('id')
                  ->on('lokasi_server')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subdomain');
    }
};