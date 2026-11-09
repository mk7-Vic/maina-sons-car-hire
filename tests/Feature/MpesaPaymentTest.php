<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MpesaPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function configureDaraja(): void
    {
        config([
            'services.mpesa.environment' => 'sandbox',
            'services.mpesa.consumer_key' => 'test-consumer-key',
            'services.mpesa.consumer_secret' => 'test-consumer-secret',
            'services.mpesa.shortcode' => '174379',
            'services.mpesa.passkey' => 'test-passkey',
            'services.mpesa.callback_url' => 'https://example.test/mpesa/callback',
            'services.mpesa.transaction_type' => 'CustomerPayBillOnline',
        ]);
    }

    private function successfulCallback(Payment $payment, string $receipt = 'QGH123ABC'): array
    {
        return [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID' => 'merchant-test-123',
                    'CheckoutRequestID' => $payment->checkout_request_id,
                    'ResultCode' => 0,
                    'ResultDesc' => 'The service request is processed successfully.',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => (int) $payment->amount],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => $receipt],
                            ['Name' => 'PhoneNumber', 'Value' => '254712345678'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_customer_can_initiate_an_mpesa_stk_push(): void
    {
        $this->configureDaraja();

        Http::fake([
            '*oauth/v1/generate*' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => '3599',
            ]),
            '*mpesa/stkpush/v1/processrequest' => Http::response([
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success. Request accepted for processing',
                'MerchantRequestID' => 'merchant-test-123',
                'CheckoutRequestID' => 'ws_CO_test_123',
                'CustomerMessage' => 'Success. Request accepted for processing',
            ]),
        ]);

        $booking = Booking::factory()->create([
            'total_amount' => 12000,
        ]);

        $this->from(route('bookings.confirmation', $booking->booking_reference))
            ->post(route('bookings.mpesa', $booking->booking_reference), [
                'phone_number' => '0712345678',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'payment_method' => 'mpesa',
            'amount' => 12000,
            'phone_number' => '254712345678',
            'status' => 'pending',
            'checkout_request_id' => 'ws_CO_test_123',
        ]);
    }

    public function test_successful_mpesa_callback_completes_payment(): void
    {
        $booking = Booking::factory()->create([
            'total_amount' => 12000,
        ]);

        $payment = $booking->payments()->create([
            'amount' => 12000,
            'payment_method' => 'mpesa',
            'phone_number' => '254712345678',
            'checkout_request_id' => 'ws_CO_success_123',
            'status' => 'pending',
        ]);

        $this->postJson(route('mpesa.callback'), $this->successfulCallback($payment))
            ->assertOk()
            ->assertJson(['ResultCode' => 0]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
            'transaction_reference' => 'QGH123ABC',
            'phone_number' => '254712345678',
        ]);

        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_failed_mpesa_callback_marks_payment_as_failed(): void
    {
        $booking = Booking::factory()->create();

        $payment = $booking->payments()->create([
            'amount' => 12000,
            'payment_method' => 'mpesa',
            'checkout_request_id' => 'ws_CO_failed_123',
            'status' => 'pending',
        ]);

        $this->postJson(route('mpesa.callback'), [
            'Body' => [
                'stkCallback' => [
                    'CheckoutRequestID' => $payment->checkout_request_id,
                    'ResultCode' => 1032,
                    'ResultDesc' => 'Request cancelled by user.',
                ],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'failed',
            'result_code' => 1032,
        ]);
    }

    public function test_callback_with_wrong_amount_does_not_complete_payment(): void
    {
        $booking = Booking::factory()->create([
            'total_amount' => 12000,
        ]);

        $payment = $booking->payments()->create([
            'amount' => 12000,
            'payment_method' => 'mpesa',
            'checkout_request_id' => 'ws_CO_wrong_amount_123',
            'status' => 'pending',
        ]);

        $callback = $this->successfulCallback($payment);
        $callback['Body']['stkCallback']['CallbackMetadata']['Item'][0]['Value'] = 5000;

        $this->postJson(route('mpesa.callback'), $callback)->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'pending',
            'transaction_reference' => null,
        ]);
    }

    public function test_duplicate_successful_callback_does_not_process_payment_twice(): void
    {
        $booking = Booking::factory()->create([
            'total_amount' => 12000,
        ]);

        $payment = $booking->payments()->create([
            'amount' => 12000,
            'payment_method' => 'mpesa',
            'checkout_request_id' => 'ws_CO_duplicate_123',
            'status' => 'pending',
        ]);

        $callback = $this->successfulCallback($payment);

        $this->postJson(route('mpesa.callback'), $callback)->assertOk();
        $this->postJson(route('mpesa.callback'), $callback)->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
            'transaction_reference' => 'QGH123ABC',
        ]);

        $this->assertDatabaseCount('payments', 1);
    }
}
