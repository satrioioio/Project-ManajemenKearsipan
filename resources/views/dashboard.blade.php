@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan arsip kontrak dan proyek konstruksi')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Total Proyek</div>
                <div class="value">{{ number_format($stats['projects'], 0, ',', '.') }}</div>
                <div class="icon icon-sky"><i class="bi bi-buildings"></i></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Dokumen Terarsip</div>
                <div class="value">{{ number_format($stats['documents'], 0, ',', '.') }}</div>
                <div class="icon icon-emerald"><i class="bi bi-folder2-open"></i></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Kontrak &amp; Addendum</div>
                <div class="value">{{ number_format($stats['contracts'], 0, ',', '.') }}</div>
                <div class="icon icon-amber"><i class="bi bi-file-earmark-text"></i></div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="label">Arsip Terkunci</div>
                <div class="value">{{ number_format($stats['locked'], 0, ',', '.') }}</div>
                <div class="icon icon-violet"><i class="bi bi-lock"></i></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card-soft h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history me-1 text-secondary"></i> Dokumen Terbaru</span>
                    <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-secondary">Lihat semua</a>
                </div>

                @if ($recentDocuments->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        Belum ada dokumen yang dapat Anda akses.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Dokumen</th>
                                    <th>Jenis</th>
                                    <th>Keamanan</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentDocuments as $document)
                                    <tr>
                                        <td>
                                            <a href="{{ route('documents.show', $document) }}" class="fw-semibold text-dark">
                                                {{ $document->judul }}
                                            </a>
                                            <div class="small text-secondary text-truncate" title="{{ $document->nomor_dokumen }}">
                                                {{ $document->nomor_dokumen }}
                                            </div>
                                        </td>
                                        <td><span class="badge bg-{{ $document->jenisColor() }} bg-opacity-10 text-{{ $document->jenisColor() }}">{{ $document->jenisLabel() }}</span></td>
                                        <td><span class="badge bg-{{ $document->securityColor() }} bg-opacity-10 text-{{ $document->securityColor() }}">{{ $document->securityLabel() }}</span></td>
                                        <td class="small text-secondary text-nowrap">{{ $document->tanggal_dokumen?->translatedFormat('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card-soft mb-4">
                <div class="card-header"><i class="bi bi-hourglass-split me-1 text-secondary"></i> Jadwal Retensi Terdekat</div>
                <div class="card-body py-2">
                    @forelse ($upcomingRetention as $document)
                        <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                            <div class="pe-2">
                                <a href="{{ route('documents.show', $document) }}" class="small fw-semibold text-dark d-block">
                                    {{ \Illuminate\Support\Str::limit($document->judul, 42) }}
                                </a>
                                <span class="small text-secondary">{{ $document->project?->kode_proyek }}</span>
                            </div>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary text-nowrap">
                                {{ $document->retention_date?->translatedFormat('d M Y') }}
                            </span>
                        </div>
                    @empty
                        <div class="empty-state py-4">
                            <i class="bi bi-calendar2-check"></i>
                            Belum ada jadwal retensi.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="card-soft">
                <div class="card-header"><i class="bi bi-graph-up-arrow me-1 text-secondary"></i> Progres Fase Proyek</div>
                <div class="card-body">
                    @forelse ($projects as $project)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <a href="{{ route('projects.show', $project) }}" class="small fw-semibold text-dark">
                                    {{ \Illuminate\Support\Str::limit($project->nama_proyek, 34) }}
                                </a>
                                <span class="small text-secondary">{{ $project->progressPercent() }}%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: {{ $project->progressPercent() }}%"></div>
                            </div>
                            <div class="small text-secondary mt-1">
                                {{ $project->phases_count }} fase &middot; {{ $project->documents_count }} dokumen
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-3">
                            <i class="bi bi-buildings"></i>
                            Belum ada proyek.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
