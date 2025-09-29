<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kode')->unique();
            $table->unsignedBigInteger('total_harga')->default(0);
            $table->enum('status', ['menunggu_pembayaran','dibayar','kedaluwarsa','batal'])->default('menunggu_pembayaran');
            $table->string('provider')->nullable();
            $table->string('provider_reference')->nullable();
            $table->timestamp('dibayar_pada')->nullable();
            $table->timestamps();
            $table->index(['kode','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
