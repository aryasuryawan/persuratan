<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_topik', function (Blueprint $table) {
            $table->id();
            $table->string('document_type');
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('topik_id');
            $table->foreign('topik_id')->references('id')->on('topik')->cascadeOnDelete();
            $table->unique(['document_type', 'document_id', 'topik_id']);
            $table->index(['document_type', 'document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_topik');
    }
};
