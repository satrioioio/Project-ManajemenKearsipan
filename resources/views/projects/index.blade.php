@extends('layouts.app')

@section('title', 'Proyek')
@section('page-title', 'Manajemen Proyek')
@section('page-subtitle', 'Daftar proyek konstruksi dan arsip dokumen terkait')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <form class="d-flex gap-2" method="GET" action="{{ route('projects.index') }}">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" style="min-width: 240px"
                placeholder="Cari nama atau kode proyek...">
            <select name="status" class="form-select" style="width: 160px">
                <option value="">Semua status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        </form>

        @if (auth()->user()->canManageDocuments())
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Proyek Baru
            </a>
        @endif
    </div>

    @if ($projects->isEmpty())
        <div class="card-soft empty-state">
            <i class="bi bi-buildings"></i>
            Belum ada proyek yang cocok dengan pencarian.
        </div>
    @else
        <div class="row g-3">
            @foreach ($projects as $project)
                <div class="col-md-6 col-xl-4">
                    <div class="card-soft h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $project->kode_proyek }}</span>
                                @php
                                    $statusColor = match ($project->status) {
                                        'selesai' => 'success',
                                        'sengketa' => 'danger',
                                        'berjalan' => 'primary',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }}">
                                    {{ $project->statusLabel() }}
                                </span>
                            </div>

                            <a href="{{ route('projects.show', $project) }}" class="h6 fw-bold text-dark mb-1">
                                {{ $project->nama_proyek }}
                            </a>
                            <div class="small text-secondary mb-3">
                                <i class="bi bi-person me-1"></i>{{ $project->pemilik_pekerjaan ?? 'Pemilik belum diisi' }}
                                @if ($project->lokasi)
                                    <span class="mx-1">&middot;</span><i class="bi bi-geo-alt me-1"></i>{{ $project->lokasi }}
                                @endif
                            </div>

                            <div class="mt-auto">
                                <div class="d-flex justify-content-between small text-secondary mb-1">
                                    <span>Progres fase</span>
                                    <span>{{ $project->progressPercent() }}%</span>
                                </div>
                                <div class="progress mb-3">
                                    <div class="progress-bar" style="width: {{ $project->progressPercent() }}%"></div>
                                </div>
                                <div class="d-flex gap-3 small text-secondary">
                                    <span><i class="bi bi-list-check me-1"></i>{{ $project->phases_count }} fase</span>
                                    <span><i class="bi bi-files me-1"></i>{{ $project->documents_count }} dokumen</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $projects->links() }}
        </div>
    @endif
@endsection
