<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class TeraApiService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected string $rsid;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.tera.backend_url'), '/');
        $this->username = (string) config('services.tera.username');
        $this->password = (string) config('services.tera.password');
        $this->rsid = (string) config('services.tera.rsid');
    }

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    public function imageUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    public function token(): string
    {
        $token = Session::get('tera_access_token');
        if ($token) {
            return $token;
        }

        $response = Http::acceptJson()->post($this->baseUrl . '/auth/v1/login', [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if ($response->successful()) {
            $token = $response->json('data.access_token') ?? $response->json('token');
            if ($token) {
                Session::put('tera_access_token', $token);
                return $token;
            }
        }

        throw new \RuntimeException('Gagal login ke backend: ' . $response->body());
    }

    public function resetToken(): void
    {
        Session::forget('tera_access_token');
    }

    protected function http()
    {
        return Http::withToken($this->token())->acceptJson();
    }

    protected function request(callable $callback)
    {
        $response = $callback($this->http());

        if ($response->unauthorized() || $response->status() === 401) {
            $this->resetToken();
            $response = $callback($this->http());
        }

        return $response;
    }

    /**
     * List dokter dari backend (sudah agregat semua halaman + foto dari DB backend).
     */
    public function listDoctors(array $filters = []): array
    {
        $response = $this->request(fn ($http) => $http->get($this->baseUrl . '/api/tera/find_doctor', array_merge([
            'rsid' => $this->rsid,
        ], $filters)));

        if (! $response->successful()) {
            return [];
        }

        $data = $response->json('data') ?? $response->json() ?? [];
        $data = is_array($data) ? $data : [];

        return array_values(array_filter($data, 'is_array'));
    }

    public function findCmsDoctor(string $pid): ?array
    {
        $response = $this->request(fn ($http) => $http->get($this->baseUrl . '/api/tera/find_cms_doctor/' . urlencode($pid)));

        if ($response->successful()) {
            $data = $response->json('data');
            return is_array($data) ? $data : null;
        }

        return null;
    }

    /**
     * Ambil data dokter langsung dari tera (untuk melengkapi poli bila kosong).
     */
    public function teraDoctor(string $pid): ?array
    {
        $response = $this->request(fn ($http) => $http->get($this->baseUrl . '/api/tera/doctor/' . urlencode($pid), [
            'rsid' => $this->rsid,
        ]));

        if ($response->successful()) {
            $data = $response->json('data');
            return is_array($data) ? $data : null;
        }

        return null;
    }

    public function updateDoctor(array $data): array
    {
        $response = $this->request(fn ($http) => $http->post($this->baseUrl . '/api/tera/update_doctor', $data));

        return $response->json() ?? [];
    }

    public function storeUpdateDoctor(array $data, $file = null): array
    {
        $send = function ($http) use ($data, $file) {
            if ($file) {
                $http = $http->attach('url_image', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName());
            }

            return $http->post($this->baseUrl . '/api/tera/store_update_doctor', $data);
        };

        $response = $this->request($send);

        return $response->json() ?? [];
    }
}