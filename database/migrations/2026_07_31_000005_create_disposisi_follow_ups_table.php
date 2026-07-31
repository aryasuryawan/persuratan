<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disposisi_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disposisi_id')->constrained('disposisi')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->text('keterangan');
            $table->json('lampiran')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposisi_follow_ups');
    }
};
