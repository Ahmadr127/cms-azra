<?php

namespace App\Http\Controllers;

use App\Http\Requests\Doctor\Store;
use App\Http\Requests\Doctor\Update;
use App\Services\TeraApiService;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function __construct(protected TeraApiService $teraApi) {}

    public function index(Request $request)
    {
        $doctors = $this->teraApi->listDoctors($request->only(['search']));

        $doctors = collect($doctors)->map(function ($doctor) {
            $doctor['image_url'] = $this->teraApi->imageUrl($doctor['url_img'] ?? $doctor['foto_profile'] ?? null);
            return $doctor;
        })->all();

        return view('doctors.index', compact('doctors'));
    }

    public function create()
    {
        $poli = $this->teraApi->getPoli();

        return view('doctors.create', compact('poli'));
    }

    public function store(Store $request)
    {
        $data = $request->validated();
        $file = $request->file('url_image');

        if ($file) {
            $this->teraApi->storeOrUpdateDoctor($data, $file);
        } else {
            $this->teraApi->updateDoctor($data);
        }

        return redirect()->route('doctors.index')->with('success', 'Dokter berhasil disimpan!');
    }

    public function edit(string $pid)
    {
        $doctor = $this->teraApi->findDoctor($pid);

        if (! $doctor) {
            return redirect()->route('doctors.index')->with('error', 'Dokter tidak ditemukan.');
        }

        $doctor['image_url'] = $this->teraApi->imageUrl($doctor['url_img'] ?? $doctor['foto_profile'] ?? null);

        $poli = $this->teraApi->getPoli();

        return view('doctors.edit', compact('doctor', 'poli'));
    }

    public function update(Update $request, string $pid)
    {
        $data = $request->validated();
        $data['pid'] = $pid;
        $file = $request->file('url_image');

        if ($file) {
            $this->teraApi->storeOrUpdateDoctor($data, $file);
        } else {
            $this->teraApi->updateDoctor($data);
        }

        return redirect()->route('doctors.index')->with('success', 'Dokter berhasil diperbarui!');
    }
}