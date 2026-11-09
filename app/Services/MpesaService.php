<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MpesaService
{
    private function baseUrl(): string
    {
        return config('services.mpesa.environment') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    private function accessToken(): string
    {
        $key = config('services.mpesa.consumer_key');
        $secret = config('services.mpesa.consumer_secret');

        if (! $key || ! $secret) {
            throw new RuntimeException('M-Pesa API credentials are not configured.');
        }

        $response = Http::withBasicAuth($key, $secret)
            ->acceptJson()
            ->timeout(20)
            ->get($this->baseUrl().'/oauth/v1/generate', [
                'grant_type' => 'client_credentials',
            ]);

        $response->throw();

        $token = $response->json('access_token');

        if (! $token) {
            throw new RuntimeException('Daraja did not return an access token.');
        }

        return $token;
    }

    public function stkPush(
        string $phone,
        int $amount,
        string $bookingReference
    ): array {
        $shortcode = config('services.mpesa.shortcode');
        $passkey = config('services.mpesa.passkey');
        $callbackUrl = config('services.mpesa.callback_url');

        if (! $shortcode || ! $passkey || ! $callbackUrl) {
            throw new RuntimeException('M-Pesa shortcode, passkey or callback URL is missing.');
        }

        if (! Str::startsWith($callbackUrl, 'https://')) {
            throw new RuntimeException('The M-Pesa callback URL must use HTTPS.');
        }

        $timestamp = now('Africa/Nairobi')->format('YmdHis');
        $password = base64_encode($shortcode.$passkey.$timestamp);

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(30)
            ->post($this->baseUrl().'/mpesa/stkpush/v1/processrequest', [
                'BusinessShortCode' => $shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => config('services.mpesa.transaction_type'),
                'Amount' => $amount,
                'PartyA' => $phone,
                'PartyB' => $shortcode,
                'PhoneNumber' => $phone,
                'CallBackURL' => $callbackUrl,
                'AccountReference' => substr($bookingReference, 0, 12),
                'TransactionDesc' => 'Vehicle hire',
            ]);

        $response->throw();

        if ($response->json('ResponseCode') !== '0') {
            throw new RuntimeException(
                $response->json('ResponseDescription', 'Daraja rejected the STK Push request.')
            );
        }

        if (! $response->json('CheckoutRequestID')) {
            throw new RuntimeException('Daraja did not return a checkout request ID.');
        }

        return $response->json();
    }

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (preg_match('/^0(7|1)\d{8}$/', $digits)) {
            $digits = '254'.substr($digits, 1);
        } elseif (preg_match('/^(7|1)\d{8}$/', $digits)) {
            $digits = '254'.$digits;
        }

        if (! preg_match('/^254(7|1)\d{8}$/', $digits)) {
            throw new RuntimeException('Enter a valid Kenyan mobile number.');
        }

        return $digits;
    }
}
