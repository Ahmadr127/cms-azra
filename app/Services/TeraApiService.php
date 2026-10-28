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
        $this->baseUrl = rtrim(config('services.tera.base_url'), '/');
        $this->username = (string) config('services.tera.username');
        $this->password = (string) config('services.tera.password');
        $this->rsid = (string) config('services.tera.rsid');
    }

    public function token(): string
    {
        $token = Session::get('tera_access_token');
        if ($token) {
            return $token;
        }

        $response = Http::acceptJson()->post($this->baseUrl . '/api/v1/login', [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if ($response->successful()) {
            $token = $response->json('token') ?? $response->json('data.access_token');
            if ($token) {
                Session::put('tera_access_token', $token);
                return $token;
            }
        }

        throw new \RuntimeException('Gagal login ke tera: ' . $response->body());
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

        if ($response->unauthorized()) {
            $this->resetToken();
            $response = $callback($this->http());
        }

        return $response;
    }

    /**
     * Ambil semua dokter dari tera (agregat paginasi, exclude POLI UMUM/LAKTASI).
     */
    public function listDoctors(): array
    {
        $page = 1;
        $all = [];
        $lastPage = 1;

        do {
            $response = $this->request(fn ($http) => $http->get($this->baseUrl . '/api/v1/doctors', [
                'rsid' => $this->rsid,
                'group' => true,
                'page' => $page,
            ]));

            if (! $response->successful()) {
                break;
            }

            $json = $response->json() ?? [];
            $root = $json['data'] ?? $json;
            $root = is_array($root) ? $root : [];

            $items = $root['data'] ?? $root;
            $items = is_array($items) ? $items : [];

            $lastPage = (int) ($root['last_page'] ?? 1);

            $hidden = ['POLI UMUM', 'POLI LAKTASI'];
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $poli = $item['poli'] ?? null;
                if ($poli !== null && in_array($poli, $hidden, true)) {
                    continue;
                }
                $all[] = $item;
            }

            $page++;
        } while ($page <= $lastPage && $page <= 100);

        return $all;
    }
}