@extends('layouts.app')

@section('title', $document->judul)
@section('page-title', 'Detail Dokumen')
@section('page-subtitle', $document->nomor_dokumen)

@section('content')
    @php
        $lifecycleColor = match ($document->lifecycle_status) {
            'active' => 'success',
            'inactive' => 'secondary',
            'pending_destruction' => 'warning',
            'permanent' => 'primary',
            'destroyed' => 'dark',
            default => 'secondary',
        };
        $days = $document->daysUntilRetention();
    @endphp

    <div class="card-soft mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-{{ $document->jenisColor() }} bg-opacity-10 text-{{ $document->jenisColor() }}">{{ $document->jenisLabel() }}</span>
                        <span class="badge bg-{{ $document->securityColor() }} bg-opacity-10 text-{{ $document->securityColor() }}">{{ $document->securityLabel() }}</span>
                        <span class="badge bg-{{ $lifecycleColor }} bg-opacity-10 text-{{ $lifecycleColor }}">{{ $document->lifecycleLabel() }}</span>
                        @if ($document->is_locked)
                            <span class="badge bg-warning bg-opacity-10 text-warning"><i class="bi bi-lock-fill me-1"></i> Arsip Terkunci</span>
                        @endif
                    </div>
                    <h2 class="h4 fw-bold mb-1">{{ $document->judul }}</h2>
                    <div class="text-secondary small">
                        <span class="code-chip">{{ $document->nomor_dokumen }}</span>
                        <span class="mx-1">&middot;</span> Versi {{ $document->versi }}
                        <span class="mx-1">&middot;</span>
                        <a href="{{ route('projects.show', $document->project) }}" class="text-secondary">
                            {{ $document->project?->nama_proyek }}
                        </a>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i>
                    </a>

                    @can('download', $document)
                        <a href="{{ route('documents.download', $document) }}" class="btn btn-primary">
                            <i class="bi bi-download me-1"></i> Unduh
                        </a>
                    @endcan

                    @can('update', $document)
                        <a href="{{ route('documents.edit', $document) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-pencil me-1"></i> Ubah
                        </a>
                    @endcan

                    @can('lock', $document)
                        <form method="POST" action="{{ route('documents.toggle-lock', $document) }}">
                            @csrf
                            <button class="btn {{ $document->is_locked ? 'btn-outline-warning' : 'btn-outline-secondary' }}" type="submit">
                                <i class="bi {{ $document->is_locked ? 'bi-unlock' : 'bi-lock' }} me-1"></i>
                                {{ $document->is_locked ? 'Buka Kunci' : 'Kunci Arsip' }}
                            </button>
                        </form>
                    @endcan

                    @can('delete', $document)
                        <form method="POST" action="{{ route('documents.destroy', $document) }}"
                            onsubmit="return confirm('Hapus dokumen ini dari daftar aktif?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                        </form>
                    @endcan
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3">
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Proyek</div>
                    <div class="meta-value">{{ $document->project?->kode_proyek ?? '-' }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Fase / Milestone</div>
                    <div class="meta-value">{{ $document->phase?->nama ?? 'Tidak terhubung' }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Klasifikasi</div>
                    <div class="meta-value">{{ $document->classification?->fullLabel() ?? '-' }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Dokumen Induk</div>
                    <div class="meta-value">
                        @if ($document->parent)
                            <a href="{{ route('documents.show', $document->parent) }}" class="text-dark">
                                {{ \Illuminate\Support\Str::limit($document->parent->judul, 34) }}
                            </a>
                        @else
                            —
                        @endif
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="meta-label">Tanggal Dokumen</div>
                    <div class="meta-value">{{ $document->tanggal_dokumen?->translatedFormat('d F Y') }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Berlaku Sejak</div>
                    <div class="meta-value">{{ $document->valid_from?->translatedFormat('d F Y') }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Berlaku Sampai</div>
                    <div class="meta-value">{{ $document->valid_until?->translatedFormat('d F Y') ?? 'Masih berlaku' }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Tanggal Retensi</div>
                    <div class="meta-value">
                        {{ $document->retention_date?->translatedFormat('d F Y') ?? '-' }}
                        @if ($days !== null)
                            <span class="badge bg-{{ $days <= 365 ? 'warning' : 'secondary' }} bg-opacity-10 text-{{ $days <= 365 ? 'warning' : 'secondary' }} ms-1">
                                {{ $days > 0 ? $days . ' hari lagi' : 'jatuh tempo' }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <div class="meta-label">Deskripsi</div>
                    <div class="meta-value">{{ $document->deskripsi ?: '—' }}</div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="meta-label">Berkas</div>
                    @if ($document->file_path)
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <i class="bi bi-file-earmark-lock text-secondary"></i>
                            <span class="meta-value">{{ $document->file_name }}</span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $document->fileSizeLabel() }}</span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $document->mime_type }}</span>
                        </div>
                        <div class="mt-2 small text-secondary">
                            SHA-256 (fixity): <span class="code-chip">{{ $document->checksum_sha256 }}</span>
                        </div>
                    @else
                        <div class="meta-value text-secondary">Belum ada berkas terunggah.</div>
                    @endif
                </div>
                <div class="col-lg-5">
                    <div class="meta-label">Pencipta Arsip</div>
                    <div class="meta-value">
                        {{ $document->creator?->name ?? 'Sistem' }}
                        <span class="text-secondary small">
                            · {{ $document->created_at?->translatedFormat('d M Y H:i') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card-soft h-100">
                <div class="card-header">
                    <i class="bi bi-clock-history me-1 text-secondary"></i> Riwayat Versi &amp; Masa Berlaku
                </div>
                <div class="card-body">
                    @forelse ($timeline as $item)
                        @php
                            $isCurrent = $item->id === $document->id;
                            $isActive = $item->isValidOn();
                            $isFuture = $item->valid_from->gt(now()->startOfDay());
                            $stateClass = $isActive ? 'is-active' : ($isFuture ? 'is-future' : '');
                        @endphp
                        <div class="timeline-version {{ $stateClass }} mb-4">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <div>
                                    <a href="{{ route('documents.show', $item) }}" class="fw-semibold text-dark">
                                        {{ $item->judul }}
                                        @if ($isCurrent)
                                            <span class="badge bg-primary bg-opacity-10 text-primary ms-1">dibuka</span>
                                        @endif
                                    </a>
                                    <div class="small text-secondary">
                                        {{ $item->nomor_dokumen }} · v{{ $item->versi }}
                                        @if ($item->parent_document_id)
                                            · addendum/revisi
                                        @endif
                                    </div>
                                </div>
                                <span class="badge bg-{{ $isActive ? 'success' : ($isFuture ? 'warning' : 'secondary') }} bg-opacity-10 text-{{ $isActive ? 'success' : ($isFuture ? 'warning' : 'secondary') }} align-self-start">
                                    {{ $isActive ? 'Berlaku' : ($isFuture ? 'Akan berlaku' : 'Tidak berlaku') }}
                                </span>
                            </div>
                            <div class="small text-secondary mt-1">
                                {{ $item->valid_from?->translatedFormat('d M Y') }}
                                &ndash;
                                {{ $item->valid_until?->translatedFormat('d M Y') ?? 'sekarang' }}
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary">Belum ada riwayat versi.</div>
                    @endforelse

                    @if ($document->children->isNotEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Dokumen ini memiliki {{ $document->children->count() }} addendum/revisi terkait.
                            Masa berlaku versi sebelumnya otomatis ditutup saat addendum baru dicatat.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card-soft h-100">
                <div class="card-header">
                    <i class="bi bi-shield-check me-1 text-secondary"></i> Jejak Akses (Audit Trail)
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="small text-secondary">
                                        {{ $log->created_at?->translatedFormat('d M Y H:i') }}
                                        <div class="text-secondary" style="font-size: 0.72rem">{{ $log->ip_address }}</div>
                                    </td>
                                    <td class="small">{{ $log->user?->name ?? 'Sistem' }}</td>
                                    <td>
                                        @php
                                            $actionColor = match ($log->action) {
                                                'download' => 'primary',
                                                'delete', 'denied' => 'danger',
                                                'lock' => 'warning',
                                                'create' => 'success',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $actionColor }} bg-opacity-10 text-{{ $actionColor }}">{{ $log->actionLabel() }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-secondary py-4">Belum ada aktivitas tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
