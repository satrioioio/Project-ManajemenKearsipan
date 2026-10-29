@extends('layouts.app')

@section('title', 'Dokumen Arsip')
@section('page-title', 'Dokumen Arsip')
@section('page-subtitle', 'Seluruh dokumen kontrak, addendum, dan pendukung proyek')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <form class="d-flex flex-wrap gap-2" method="GET" action="{{ route('documents.index') }}">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" style="min-width: 220px"
                placeholder="Cari judul atau nomor dokumen...">
            <select name="project" class="form-select" style="width: 200px">
                <option value="">Semua proyek</option>
                @foreach ($projects as $project)
                    <option value="{{ $project->id }}" @selected((string) request('project') === (string) $project->id)>
                        {{ $project->kode_proyek }}
                    </option>
                @endforeach
            </select>
            <select name="jenis" class="form-select" style="width: 165px">
                <option value="">Semua jenis</option>
                @foreach ($jenisList as $value => $label)
                    <option value="{{ $value }}" @selected(request('jenis') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="security" class="form-select" style="width: 160px">
                <option value="">Semua keamanan</option>
                @foreach ($securityList as $value => $label)
                    <option value="{{ $value }}" @selected(request('security') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        </form>

        @if (auth()->user()->canManageDocuments())
            <a href="{{ route('documents.create') }}" class="btn btn-primary">
                <i class="bi bi-cloud-arrow-up me-1"></i> Unggah Dokumen
            </a>
        @endif
    </div>

    <div class="card-soft">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Dokumen</th>
                        <th>Proyek</th>
                        <th>Jenis</th>
                        <th>Masa Berlaku</th>
                        <th>Keamanan</th>
                        <th>Retensi</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td style="min-width: 230px">
                                <a href="{{ route('documents.show', $document) }}" class="fw-semibold text-dark d-block">
                                    {{ \Illuminate\Support\Str::limit($document->judul, 46) }}
                                </a>
                                <div class="small text-secondary">
                                    {{ $document->nomor_dokumen }} · v{{ $document->versi }}
                                    @if ($document->is_locked)
                                        <i class="bi bi-lock-fill text-warning ms-1" title="Arsip terkunci"></i>
                                    @endif
                                </div>
                            </td>
                            <td class="small">
                                {{ $document->project?->kode_proyek ?? '-' }}
                                <div class="text-secondary">{{ $document->phase?->nama ?? 'Tanpa fase' }}</div>
                            </td>
                            <td><span class="badge bg-{{ $document->jenisColor() }} bg-opacity-10 text-{{ $document->jenisColor() }}">{{ $document->jenisLabel() }}</span></td>
                            <td class="small text-secondary">
                                {{ $document->valid_from?->translatedFormat('d M Y') }}
                                @if ($document->valid_until)
                                    <br>s.d. {{ $document->valid_until->translatedFormat('d M Y') }}
                                @else
                                    <br><span class="text-success">masih berlaku</span>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ $document->securityColor() }} bg-opacity-10 text-{{ $document->securityColor() }}">{{ $document->securityLabel() }}</span></td>
                            <td class="small text-secondary">{{ $document->retention_date?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-outline-secondary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('download', $document)
                                    <a href="{{ route('documents.download', $document) }}" class="btn btn-sm btn-outline-primary" title="Unduh">
                                        <i class="bi bi-download"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="bi bi-inbox"></i>
                                Tidak ada dokumen yang cocok atau dapat Anda akses.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $documents->links() }}</div>
@endsection
