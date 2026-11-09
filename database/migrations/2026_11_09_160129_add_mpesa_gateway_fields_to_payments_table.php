<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('merchant_request_id')->nullable()->index();
            $table->string('checkout_request_id')->nullable()->unique();
            $table->string('phone_number', 20)->nullable();
            $table->integer('result_code')->nullable();
            $table->text('result_description')->nullable();
            $table->json('gateway_response')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['checkout_request_id']);
            $table->dropIndex(['merchant_request_id']);
            $table->dropColumn([
                'merchant_request_id',
                'checkout_request_id',
                'phone_number',
                'result_code',
                'result_description',
                'gateway_response',
            ]);
        });
    }
};
