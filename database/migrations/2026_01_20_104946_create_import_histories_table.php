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
        Schema::create('import_histories', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['success', 'failure']);
            $table->dateTime('imported_at');
            $table->json('processed_files')->nullable();
            $table->integer('total_products')->default(0);
            $table->text('error')->nullable();
            $table->string('memory_usage')->nullable();
            $table->string('execution_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_histories');
    }
};
