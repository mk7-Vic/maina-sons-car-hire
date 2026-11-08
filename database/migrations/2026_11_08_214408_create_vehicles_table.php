<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('registration_number')->unique();
            $table->string('make');
            $table->string('model');
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('color')->nullable();

            $table->string('transmission')->nullable();
            $table->string('fuel_type')->nullable();
            $table->unsignedTinyInteger('seats')->default(5);

            $table->decimal('daily_rate', 10, 2);
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();

            $table->string('status')->default('available')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
