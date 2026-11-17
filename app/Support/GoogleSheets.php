<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Pembaca Google Sheets memakai service account, tanpa library tambahan.
 * Alur: tanda tangani JWT dengan private key → tukar ke access token → panggil Sheets API.
 */
class GoogleSheets
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const API_URL   = 'https://sheets.googleapis.com/v4/spreadsheets';
    private const SCOPE     = 'https://www.googleapis.com/auth/spreadsheets.readonly';

    /**
     * Ambil beberapa range sekaligus dalam satu permintaan.
     * Hasil: [range => array baris], nilai mentah (angka tetap angka, persen = pecahan 0.965).
     */
    public function batchGet(string $spreadsheetId, array $ranges): array
    {
        $query = collect($ranges)->map(fn ($r) => 'ranges=' . rawurlencode($r))->join('&');

        $response = $this->client()
            ->withToken($this->accessToken())
            ->get(self::API_URL . "/{$spreadsheetId}/values:batchGet?{$query}&valueRenderOption=UNFORMATTED_VALUE");

        if (! $response->successful()) {
            throw new RuntimeException('Google Sheets: ' . ($response->json('error.message') ?? "HTTP {$response->status()}"));
        }

        $result = [];
        foreach ($response->json('valueRanges', []) as $i => $valueRange) {
            $result[$ranges[$i]] = $valueRange['values'] ?? [];
        }

        return $result;
    }

    // Access token berlaku 1 jam; disimpan 50 menit
    private function accessToken(): string
    {
        return Cache::remember('google_sheets_token', now()->addMinutes(50), function () {
            $key = $this->credentials();
            $now = time();

            $jwt = $this->jwt([
                'iss'   => $key['client_email'],
                'scope' => self::SCOPE,
                'aud'   => self::TOKEN_URL,
                'iat'   => $now,
                'exp'   => $now + 3600,
            ], $key['private_key']);

            $response = $this->client()->asForm()->post(self::TOKEN_URL, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            $token = $response->json('access_token');
            if (! $response->successful() || ! $token) {
                throw new RuntimeException('Login Google ditolak: ' . ($response->json('error_description') ?? $response->json('error') ?? "HTTP {$response->status()}"));
            }

            return $token;
        });
    }

    // Kunci dari env (Vercel) atau dari file (laptop)
    private function credentials(): array
    {
        $raw = config('google.service_account.json');

        if ($raw) {
            $raw = trim($raw);
            // Boleh berupa JSON langsung atau base64 dari JSON
            if (! str_starts_with($raw, '{')) {
                $raw = base64_decode($raw, true) ?: '';
            }
        } else {
            $file = config('google.service_account.file');
            if (! $file || ! is_readable($file)) {
                throw new RuntimeException('Kunci service account Google belum diatur.');
            }
            $raw = file_get_contents($file);
        }

        $key = json_decode($raw, true);
        if (empty($key['client_email']) || empty($key['private_key'])) {
            throw new RuntimeException('Kunci service account Google tidak valid.');
        }

        return $key;
    }

    private function jwt(array $payload, string $privateKey): string
    {
        $b64 = fn (string $d) => rtrim(strtr(base64_encode($d), '+/', '-_'), '=');

        $unsigned = $b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])) . '.' . $b64(json_encode($payload));

        if (! openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Gagal menandatangani token Google.');
        }

        return $unsigned . '.' . $b64($signature);
    }

    // Timeout pendek + coba ulang sampai 4x dengan jeda makin panjang,
    // supaya tetap dapat data walau jaringan putus-sambung, tanpa membuat halaman menggantung lama
    private function client()
    {
        return Http::connectTimeout(4)
            ->timeout(8)
            ->retry([300, 800, 1500], throw: false)
            ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]]);
    }
}