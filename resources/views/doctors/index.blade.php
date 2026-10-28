@extends('layouts.app')

@section('title', 'Kelola Dokter')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-900">Kelola Dokter</h2>
        <a href="{{ route('doctors.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-white rounded-md bg-sp-primary hover:bg-sp-primary-dark transition-colors">
            <i class="bi bi-plus-lg"></i> Tambah Dokter
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Poli</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spesialis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($doctors as $doctor)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $doctor['pid'] ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $doctor['name_real'] ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $doctor['poli'] ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $doctor['spesialis'] ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(!empty($doctor['image_url']))
                                    <img src="{{ $doctor['image_url'] }}" alt="foto" class="h-10 w-10 rounded object-cover" referrerpolicy="no-referrer">
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('doctors.edit', $doctor['pid']) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data dokter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection