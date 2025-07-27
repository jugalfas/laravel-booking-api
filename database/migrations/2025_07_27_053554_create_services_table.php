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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->integer('duration'); // duration in minutes
            $table->decimal('discount', 10, 2)->default(0);
            $table->boolean('advance_payment')->default(false);
            $table->decimal('advance_payment_value', 10, 2)->nullable();
            $table->enum('advance_payment_type', ['fixed', 'percentage'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
