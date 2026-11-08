<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->string('booking_reference')->unique();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('vehicle_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('pickup_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('return_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            // Customer information (required for guest bookings)
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 30);

            // Rental period
            $table->dateTime('pickup_at');
            $table->dateTime('return_at');
            $table->unsignedSmallInteger('rental_days');

            // Price snapshot at the time of booking
            $table->decimal('daily_rate', 10, 2);
            $table->decimal('total_amount', 10, 2);

            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['vehicle_id', 'pickup_at', 'return_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
