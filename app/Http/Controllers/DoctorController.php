<?php

namespace App\Http\Controllers;

use App\Http\Requests\Doctor\Update;
use App\Models\Doctor;
use App\Models\DoctorDetail;
use App\Services\TeraApiService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    public function __construct(protected TeraApiService $teraApi) {}

    /**
     * Resolve URL gambar dari nilai url_image di DB / path relatif / URL tera.
     */
    protected function resolveImage(?string $dbPath, ?string $fallbackProfile, string $pid): string
    {
        if ($dbPath) {
            $relative = preg_replace('#^/?public/#', '', $dbPath);
            $relative = ltrim($relative, '/');

            // "default.png" di DB bukan foto asli; biarkan jatuh ke file per-pid / foto tera.
            if (! preg_match('#(^|/)default\.(png|jpe?g)$#i', $relative)
                && is_file(storage_path('app/public/' . $relative))) {
                return asset('storage/' . $relative);
            }
        }

        foreach (['jpg', 'jpeg', 'png'] as $ext) {
            $relative = 'img/doctor/' . $pid . '.' . $ext;
            if (is_file(storage_path('app/public/' . $relative))) {
                return asset('storage/' . $relative);
            }
        }

        if ($fallbackProfile) {
            return $fallbackProfile;
        }

        return asset('storage/img/doctor/default.png');
    }

    public function index(Request $request)
    {
        $teraDoctors = collect($this->teraApi->listDoctors());

        $locals = Doctor::query()->get()->keyBy(fn ($d) => (string) $d->pid);

        $rows = $teraDoctors->map(function ($doctor) use ($locals) {
            $pid = (string) ($doctor['pid'] ?? '');
            $local = $locals->get($pid);

            return [
                'pid' => $pid,
                'name_real' => $local->name_real ?? ($doctor['name_real'] ?? ''),
                'poli' => $local->poli ?? ($doctor['poli'] ?? ''),
                'sex' => $local->sex ?? ($doctor['sex'] ?? ''),
                'spesialis' => $local->spesialis ?? ($doctor['spesialis'] ?? ''),
                'image_url' => $this->resolveImage($local->url_image ?? null, $doctor['foto_profile'] ?? null, $pid),
                'last_update' => $local->last_update ?? null,
            ];
        });

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $rows = $rows->filter(function ($row) use ($needle) {
                return mb_strpos(mb_strtolower((string) $row['name_real']), $needle) !== false
                    || mb_strpos(mb_strtolower((string) $row['poli']), $needle) !== false;
            })->values();
        }

        $perPage = in_array((int) $request->query('per_page', 15), [15, 20, 50, 100]) ? (int) $request->query('per_page', 15) : 15;
        $page = max(1, (int) $request->query('page', 1));

        $doctors = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('doctors.index', compact('doctors', 'search'));
    }

    /**
     * Upsert data dokter lokal dari baris terpilih (seperti api/tera/update_doctor).
     */
    public function prepare(Request $request)
    {
        $data = $request->validate([
            'pid' => 'required|string|max:255',
            'name_real' => 'nullable|string|max:255',
            'poli' => 'nullable|string|max:255',
            'sex' => 'nullable|string|max:255',
            'spesialis' => 'nullable|string|max:255',
        ]);

        $doctor = Doctor::find($data['pid']);

        if ($doctor) {
            $doctor->fill([
                'name_real' => $data['name_real'] ?: $doctor->name_real,
            ]);
            if (! $doctor->poli && ! empty($data['poli'])) {
                $doctor->poli = $data['poli'];
            }
            if (! $doctor->sex && ! empty($data['sex'])) {
                $doctor->sex = $data['sex'];
            }
            $doctor->last_update = now();
            $doctor->save();
        } else {
            Doctor::create([
                'pid' => $data['pid'],
                'name_real' => $data['name_real'] ?: null,
                'poli' => $data['poli'] ?: null,
                'sex' => $data['sex'] ?: null,
                'spesialis' => $data['spesialis'] ?: null,
                'url_image' => 'public/img/doctor/default.png',
                'last_update' => now(),
            ]);
        }

        return redirect()->route('doctors.edit', $data['pid']);
    }

    public function edit(string $pid)
    {
        $doctor = Doctor::with('details')->find($pid);

        if (! $doctor) {
            return redirect()->route('doctors.index')->with('error', 'Dokter belum disiapkan. Klik "Kelola" dari daftar.');
        }

        $riwayat = $doctor->details->where('status', 1)->values();
        $kursus = $doctor->details->where('status', 2)->values();
        $imageUrl = $this->resolveImage($doctor->url_image, null, (string) $doctor->pid);

        return view('doctors.edit', compact('doctor', 'riwayat', 'kursus', 'imageUrl'));
    }

    public function update(Update $request, string $pid)
    {
        $doctor = Doctor::find($pid);

        if (! $doctor) {
            $doctor = new Doctor(['pid' => $pid]);
        }

        $data = $request->validated();

        $doctor->name_real = $data['name_real'] ?? $doctor->name_real;
        $doctor->poli = $data['poli'] ?? $doctor->poli;
        $doctor->sex = $data['sex'] ?? $doctor->sex;
        $doctor->spesialis = $data['spesialis'] ?? null;

        if ($file = $request->file('url_image')) {
            $filename = $pid . '.' . strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $file->storeAs('img/doctor', $filename, 'public');
            $doctor->url_image = 'public/img/doctor/' . $filename;
        } else {
            $doctor->url_image = $doctor->url_image ?: 'public/img/doctor/default.png';
        }

        $doctor->last_update = now();
        $doctor->save();

        DoctorDetail::where('pid', $pid)->delete();

        foreach (($data['riwayat'] ?? []) as $item) {
            if (empty($item['description'])) {
                continue;
            }
            DoctorDetail::create([
                'id' => (string) Str::uuid(),
                'pid' => $pid,
                'description' => $item['description'],
                'status' => 1,
            ]);
        }

        foreach (($data['kursus'] ?? []) as $item) {
            if (empty($item['description'])) {
                continue;
            }
            DoctorDetail::create([
                'id' => (string) Str::uuid(),
                'pid' => $pid,
                'description' => $item['description'],
                'status' => 2,
            ]);
        }

        return redirect()->route('doctors.index')->with('success', 'Data dokter berhasil disimpan!');
    }
}