@extends('layouts.app')

@section('title', 'Klasifikasi Arsip')
@section('page-title', 'Klasifikasi Arsip')
@section('page-subtitle', 'Daftar klasifikasi masalah dan jadwal retensi arsip')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <form class="d-flex gap-2" method="GET" action="{{ route('classifications.index') }}">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" style="min-width: 240px"
                placeholder="Cari kode atau nama klasifikasi...">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        </form>

        <a href="{{ route('classifications.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Klasifikasi Baru
        </a>
    </div>

    <div class="card-soft">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Klasifikasi</th>
                        <th>Induk</th>
                        <th>Retensi</th>
                        <th>Dokumen</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classifications as $classification)
                        <tr>
                            <td><span class="code-chip">{{ $classification->kode }}</span></td>
                            <td>
                                <div class="fw-semibold">{{ $classification->nama }}</div>
                                @if ($classification->keterangan)
                                    <div class="small text-secondary">{{ \Illuminate\Support\Str::limit($classification->keterangan, 70) }}</div>
                                @endif
                            </td>
                            <td class="small text-secondary">{{ $classification->parent?->nama ?? '—' }}</td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $classification->retensi_tahun }} tahun</span></td>
                            <td class="small text-secondary">{{ $classification->documents_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('classifications.edit', $classification) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form class="d-inline" method="POST" action="{{ route('classifications.destroy', $classification) }}"
                                    onsubmit="return confirm('Hapus klasifikasi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="bi bi-diagram-3"></i>
                                Belum ada klasifikasi arsip.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $classifications->links() }}</div>
@endsection
