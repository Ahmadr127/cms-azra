@extends('layouts.app')

@section('title', 'Edit Dokter')

@section('content')
<div class="space-y-5" x-data="doctorForm({{ Js::from($riwayat->map(fn ($d) => ['description' => $d->description])->values()) }}, {{ Js::from($kursus->map(fn ($d) => ['description' => $d->description])->values()) }})">
    <div class="flex items-center gap-3">
        <a href="{{ route('doctors.index') }}" class="inline-flex h-8 w-8 items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-50">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Profil Dokter</h2>
    </div>

    @if(session('success'))
        <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <form action="{{ route('doctors.update', $doctor->pid) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="bg-white shadow-sm rounded-lg p-6 grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="md:col-span-2 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-sp-navy mb-1">Poli</label>
                    <input type="text" name="poli" value="{{ old('poli', $doctor->poli) }}" readonly class="w-full text-sm px-3 py-2 border border-gray-200 bg-gray-50 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-sp-navy mb-1">Doctor Name</label>
                    <input type="text" name="name_real" value="{{ old('name_real', $doctor->name_real) }}" readonly class="w-full text-sm px-3 py-2 border border-gray-200 bg-gray-50 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-sp-navy mb-1">Spesialis</label>
                    <input type="text" name="spesialis" value="{{ old('spesialis', $doctor->spesialis) }}" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <input type="hidden" name="sex" value="{{ $doctor->sex }}">
                <div>
                    <label class="block text-sm font-semibold text-sp-navy mb-1">Foto</label>
                    <input type="file" name="url_image" accept="image/*" class="w-full text-sm px-3 py-2 border border-gray-300 rounded-md transition-colors cursor-pointer hover:bg-gray-50 hover:border-sp-primary file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-sp-primary file:text-white file:cursor-pointer hover:file:bg-sp-primary-dark">
                    @error('url_image') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex items-start justify-center">
                <img src="{{ $imageUrl }}" alt="foto" width="96" height="96" class="rounded-lg border" style="width: 96px; height: 96px; object-fit: cover;" referrerpolicy="no-referrer">
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6 space-y-6">
            <div>
                <h3 class="font-bold text-gray-800 mb-3">Riwayat Pendidikan</h3>
                <div class="flex gap-2 mb-3">
                    <input type="text" x-model="newRiwayat" placeholder="Tambah riwayat pendidikan"
                        class="flex-1 text-sm px-3 py-2 border border-gray-300 rounded-md">
                    <button type="button" @click="addRiwayat()" class="px-3 py-2 text-sm font-semibold text-white rounded-md bg-emerald-600 hover:bg-emerald-700">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-sp-primary">
                        <tr><th class="px-4 py-2 text-left text-xs font-medium text-white uppercase">Description</th><th class="w-24"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in riwayat" :key="'r' + index">
                            <tr>
                                <td class="px-4 py-2">
                                    <input type="text" :name="`riwayat[${index}][description]`" x-model="item.description"
                                        class="w-full text-sm px-3 py-1.5 border border-gray-200 rounded-md">
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <button type="button" @click="removeRiwayat(index)" class="text-red-600 hover:text-red-800"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="riwayat.length === 0"><td colspan="2" class="px-4 py-3 text-center text-gray-400 text-sm">Belum ada data.</td></tr>
                    </tbody>
                </table>
            </div>

            <div>
                <h3 class="font-bold text-gray-800 mb-3">Kursus &amp; Pelatihan</h3>
                <div class="flex gap-2 mb-3">
                    <input type="text" x-model="newKursus" placeholder="Tambah kursus/pelatihan"
                        class="flex-1 text-sm px-3 py-2 border border-gray-300 rounded-md">
                    <button type="button" @click="addKursus()" class="px-3 py-2 text-sm font-semibold text-white rounded-md bg-emerald-600 hover:bg-emerald-700">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-sp-primary">
                        <tr><th class="px-4 py-2 text-left text-xs font-medium text-white uppercase">Description</th><th class="w-24"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(item, index) in kursus" :key="'k' + index">
                            <tr>
                                <td class="px-4 py-2">
                                    <input type="text" :name="`kursus[${index}][description]`" x-model="item.description"
                                        class="w-full text-sm px-3 py-1.5 border border-gray-200 rounded-md">
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <button type="button" @click="removeKursus(index)" class="text-red-600 hover:text-red-800"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="kursus.length === 0"><td colspan="2" class="px-4 py-3 text-center text-gray-400 text-sm">Belum ada data.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('doctors.index') }}" class="px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-300 rounded-md">Batal</a>
            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-md bg-sp-primary hover:bg-sp-primary-dark">
                <i class="bi bi-save"></i> Simpan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('doctorForm', (riwayat, kursus) => ({
        riwayat: riwayat || [],
        kursus: kursus || [],
        newRiwayat: '',
        newKursus: '',
        addRiwayat() {
            if (!this.newRiwayat.trim()) return;
            this.riwayat.push({ description: this.newRiwayat });
            this.newRiwayat = '';
        },
        removeRiwayat(index) {
            this.riwayat.splice(index, 1);
        },
        addKursus() {
            if (!this.newKursus.trim()) return;
            this.kursus.push({ description: this.newKursus });
            this.newKursus = '';
        },
        removeKursus(index) {
            this.kursus.splice(index, 1);
        },
    }));
});
</script>
@endpush