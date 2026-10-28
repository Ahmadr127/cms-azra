@extends('layouts.app')

@section('title', 'List Of Data Doctor')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-2xl font-bold text-gray-900">List Of Data Doctor</h2>
        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">
            <div class="relative">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Global Search"
                    class="pl-8 pr-3 py-1.5 text-sm border border-gray-300 rounded-md bg-white focus:ring-2 focus:ring-sp-primary/20 focus:border-sp-primary outline-none w-64">
            </div>
            <button type="submit" class="px-3 py-1.5 text-sm font-semibold text-white rounded-md bg-sp-primary hover:bg-sp-primary-dark">Cari</button>
        </form>
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
                <thead class="bg-sp-primary">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider w-[80px]">Gambar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Doctor Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Poli</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Last Modified</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($doctors as $doctor)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <img src="{{ $doctor['image_url'] }}" alt="foto" width="48" height="48" class="rounded object-cover" style="width: 48px; height: 48px; object-fit: cover;" referrerpolicy="no-referrer">
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $doctor['name_real'] ?: '-' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $doctor['poli'] ?: '' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">
                                {{ $doctor['last_update'] ? $doctor['last_update']->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-3 text-sm">
                                <form action="{{ route('doctors.prepare') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="pid" value="{{ $doctor['pid'] }}">
                                    <input type="hidden" name="name_real" value="{{ $doctor['name_real'] }}">
                                    <input type="hidden" name="poli" value="{{ $doctor['poli'] }}">
                                    <input type="hidden" name="sex" value="{{ $doctor['sex'] }}">
                                    <input type="hidden" name="spesialis" value="{{ $doctor['spesialis'] }}">
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white rounded bg-sp-primary hover:bg-sp-primary-dark">
                                        <i class="bi bi-pencil-square"></i> Kelola
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Tidak ada data dokter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 px-4 py-3 border-t border-gray-100">
            <form method="GET" class="flex items-center gap-2 text-xs text-gray-500">
                <input type="hidden" name="search" value="{{ $search }}">
                <span>Tampilkan</span>
                <select name="per_page" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded px-2 py-1">
                    @foreach([15, 20, 50] as $size)
                        <option value="{{ $size }}" {{ (int) request('per_page', 15) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
                <span>data</span>
            </form>
            <div class="text-sm">{{ $doctors->links() }}</div>
        </div>
    </div>
</div>
@endsection