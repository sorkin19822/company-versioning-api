<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->restrictOnUpdate();
            $table->unsignedSmallInteger('version');
            $table->string('name', 256);
            $table->string('edrpou', 10);
            $table->text('address');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['company_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_versions');
    }
};
