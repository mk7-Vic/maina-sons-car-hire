<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained()
                ->restrictOnDelete();

            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->string('transaction_reference')->nullable()->unique();
            $table->string('status')->default('pending')->index();
            $table->dateTime('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
