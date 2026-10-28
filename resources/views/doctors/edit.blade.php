@extends('layouts.app')

@section('title', 'Edit Dokter')

@section('content')
<div class="max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-900 mb-4">Edit Dokter</h2>

    <form action="{{ route('doctors.update', $doctor['pid']) }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-sp-navy mb-1">PID</label>
            <input type="text" name="pid" value="{{ old('pid', $doctor['pid'] ?? '') }}" required class="w-full text-sm px-3 py-2 border border-gray-300 rounded-md">
        </div>

        <div>
            <label class="block text-sm font-semibold text-sp-navy mb-1">Nama</label>
            <input type="text" name="name_real" value="{{ old('name_real', $doctor['name_real'] ?? '') }}" required class="w-full text-sm px-3 py-2 border border-gray-300 rounded-md">
        </div>

        <div>
            <label class="block text-sm font-semibold text-sp-navy mb-1">Poli</label>
            <input type="text" name="poli" value="{{ old('poli', $doctor['poli'] ?? '') }}" required class="w-full text-sm px-3 py-2 border border-gray-300 rounded-md">
        </div>

        <div>
            <label class="block text-sm font-semibold text-sp-navy mb-1">Spesialis</label>
            <input type="text" name="spesialis" value="{{ old('spesialis', $doctor['spesialis'] ?? '') }}" required class="w-full text-sm px-3 py-2 border border-gray-300 rounded-md">
        </div>

        <div>
            <label class="block text-sm font-semibold text-sp-navy mb-1">Foto (opsional)</label>
            @if(!empty($doctor['image_url']))
                <div class="mb-2"><img src="{{ $doctor['image_url'] }}" alt="" class="h-16 w-16 rounded object-cover" referrerpolicy="no-referrer"></div>
            @endif
            <input type="file" name="url_image" class="w-full text-sm">
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('doctors.index') }}" class="px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-300 rounded-md">Batal</a>
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white rounded-md bg-sp-primary hover:bg-sp-primary-dark">Simpan</button>
        </div>
    </form>
</div>
@endsection