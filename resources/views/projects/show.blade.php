@extends('layouts.app')

@section('title', $project->nama_proyek)
@section('page-title', 'Detail Proyek')
@section('page-subtitle', $project->kode_proyek . ' · ' . $project->nama_proyek)

@section('content')
    @php
        $canManage = auth()->user()->canManageDocuments();
        $statusColor = match ($project->status) {
            'selesai' => 'success',
            'sengketa' => 'danger',
            'berjalan' => 'primary',
            default => 'secondary',
        };
    @endphp

    <div class="card-soft mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $project->kode_proyek }}</span>
                        <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }}">{{ $project->statusLabel() }}</span>
                    </div>
                    <h2 class="h4 fw-bold mb-2">{{ $project->nama_proyek }}</h2>
                    <p class="text-secondary mb-0" style="max-width: 720px">
                        {{ $project->deskripsi ?: 'Belum ada deskripsi proyek.' }}
                    </p>
                </div>

                @if ($canManage)
                    <div class="d-flex gap-2">
                        <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-pencil me-1"></i> Ubah
                        </a>
                        <form method="POST" action="{{ route('projects.destroy', $project) }}"
                            onsubmit="return confirm('Hapus proyek ini? Dokumen yang masih terkait akan mencegah penghapusan.')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                @endif
            </div>

            <hr class="my-4">

            <div class="row g-3">
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Pemilik Pekerjaan</div>
                    <div class="meta-value">{{ $project->pemilik_pekerjaan ?? '-' }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Lokasi</div>
                    <div class="meta-value">{{ $project->lokasi ?? '-' }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Nilai Kontrak</div>
                    <div class="meta-value">
                        {{ $project->nilai_kontrak ? 'Rp ' . number_format($project->nilai_kontrak, 0, ',', '.') : '-' }}
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Progres Fase</div>
                    <div class="meta-value">{{ $project->progressPercent() }}%</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Tanggal Mulai</div>
                    <div class="meta-value">{{ $project->tanggal_mulai?->translatedFormat('d F Y') ?? '-' }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Tanggal Selesai</div>
                    <div class="meta-value">{{ $project->tanggal_selesai?->translatedFormat('d F Y') ?? '-' }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Dokumen Terarsip</div>
                    <div class="meta-value">{{ $documents->count() }} dokumen</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="meta-label">Fase Proyek</div>
                    <div class="meta-value">{{ $phases->count() }} fase</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xxl-5">
            <div class="card-soft h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-check me-1 text-secondary"></i> Fase &amp; Milestone</span>
                    @if ($canManage)
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#phaseCreate">
                            <i class="bi bi-plus-lg"></i> Fase
                        </button>
                    @endif
                </div>

                @if ($canManage)
                    <div class="collapse border-bottom" id="phaseCreate">
                        <form method="POST" action="{{ route('phases.store', $project) }}" class="p-3 bg-light">
                            @csrf
                            <div class="row g-2">
                                <div class="col-8">
                                    <input type="text" name="nama" class="form-control form-control-sm" placeholder="Nama fase, mis. Pelaksanaan Struktur" required>
                                </div>
                                <div class="col-4">
                                    <input type="number" name="urutan" class="form-control form-control-sm" value="{{ $phases->max('urutan') + 1 }}" min="1" required>
                                </div>
                                <div class="col-6">
                                    <input type="date" name="tanggal_mulai" class="form-control form-control-sm" title="Tanggal mulai">
                                </div>
                                <div class="col-6">
                                    <input type="date" name="tanggal_selesai" class="form-control form-control-sm" title="Tanggal selesai">
                                </div>
                                <div class="col-8">
                                    <select name="status" class="form-select form-select-sm">
                                        @foreach (\App\Models\ProjectPhase::STATUSES as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-sm btn-primary w-100" type="submit">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="width:42px">#</th>
                                <th>Fase</th>
                                <th>Periode</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($phases as $phase)
                                @php
                                    $phaseColor = match ($phase->status) {
                                        'selesai' => 'success',
                                        'berjalan' => 'primary',
                                        default => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-secondary">{{ $phase->urutan }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $phase->nama }}</div>
                                        <div class="small text-secondary">{{ $phase->visible_documents_count }} dokumen</div>
                                    </td>
                                    <td class="small text-secondary">
                                        {{ $phase->tanggal_mulai?->translatedFormat('d M Y') ?? '—' }}
                                        <br>s.d. {{ $phase->tanggal_selesai?->translatedFormat('d M Y') ?? '—' }}
                                    </td>
                                    <td><span class="badge bg-{{ $phaseColor }} bg-opacity-10 text-{{ $phaseColor }}">{{ $phase->statusLabel() }}</span></td>
                                    <td class="text-end text-nowrap">
                                        @if ($canManage)
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                                data-bs-target="#phaseEdit{{ $phase->id }}"><i class="bi bi-pencil"></i></button>
                                            <form class="d-inline" method="POST" action="{{ route('phases.destroy', [$project, $phase]) }}"
                                                onsubmit="return confirm('Hapus fase ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">Belum ada fase proyek.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xxl-7">
            <div class="card-soft h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-folder2-open me-1 text-secondary"></i> Dokumen Proyek</span>
                    @if ($canManage)
                        <a href="{{ route('documents.create', ['project' => $project->id]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-cloud-arrow-up"></i> Unggah
                        </a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Dokumen</th>
                                <th>Jenis</th>
                                <th>Fase</th>
                                <th>Masa Berlaku</th>
                                <th>Keamanan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($documents as $document)
                                <tr>
                                    <td>
                                        <a href="{{ route('documents.show', $document) }}" class="fw-semibold text-dark">
                                            {{ \Illuminate\Support\Str::limit($document->judul, 44) }}
                                        </a>
                                        <div class="small text-secondary">{{ $document->nomor_dokumen }} · v{{ $document->versi }}</div>
                                    </td>
                                    <td><span class="badge bg-{{ $document->jenisColor() }} bg-opacity-10 text-{{ $document->jenisColor() }}">{{ $document->jenisLabel() }}</span></td>
                                    <td class="small">{{ $document->phase?->nama ?? '—' }}</td>
                                    <td class="small text-secondary">
                                        {{ $document->valid_from?->translatedFormat('d M Y') }}
                                        <br>s.d. {{ $document->valid_until?->translatedFormat('d M Y') ?? 'sekarang' }}
                                    </td>
                                    <td><span class="badge bg-{{ $document->securityColor() }} bg-opacity-10 text-{{ $document->securityColor() }}">{{ $document->securityLabel() }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">
                                        Belum ada dokumen yang dapat Anda akses pada proyek ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if ($canManage)
        @foreach ($phases as $phase)
            <div class="modal fade" id="phaseEdit{{ $phase->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('phases.update', [$project, $phase]) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title h6 fw-bold">Ubah Fase: {{ $phase->nama }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-8">
                                    <label class="form-label">Nama Fase</label>
                                    <input type="text" name="nama" class="form-control" value="{{ $phase->nama }}" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Urutan</label>
                                    <input type="number" name="urutan" class="form-control" value="{{ $phase->urutan }}" min="1" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" name="tanggal_mulai" class="form-control" value="{{ $phase->tanggal_mulai?->format('Y-m-d') }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai" class="form-control" value="{{ $phase->tanggal_selesai?->format('Y-m-d') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        @foreach (\App\Models\ProjectPhase::STATUSES as $value => $label)
                                            <option value="{{ $value }}" @selected($phase->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control" rows="2">{{ $phase->deskripsi }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif
@endsection
