@php
    $isEdit = $project->exists;
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label" for="kode_proyek">Kode Proyek <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('kode_proyek') is-invalid @enderror" id="kode_proyek"
            name="kode_proyek" value="{{ old('kode_proyek', $project->kode_proyek) }}" placeholder="PRJ-2021-001" required>
        @error('kode_proyek') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-8">
        <label class="form-label" for="nama_proyek">Nama Proyek <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('nama_proyek') is-invalid @enderror" id="nama_proyek"
            name="nama_proyek" value="{{ old('nama_proyek', $project->nama_proyek) }}"
            placeholder="Pembangunan Gedung Kantor Regional" required>
        @error('nama_proyek') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="pemilik_pekerjaan">Pemilik Pekerjaan</label>
        <input type="text" class="form-control @error('pemilik_pekerjaan') is-invalid @enderror"
            id="pemilik_pekerjaan" name="pemilik_pekerjaan"
            value="{{ old('pemilik_pekerjaan', $project->pemilik_pekerjaan) }}" placeholder="PT Nusantara Energi">
        @error('pemilik_pekerjaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="lokasi">Lokasi</label>
        <input type="text" class="form-control @error('lokasi') is-invalid @enderror" id="lokasi"
            name="lokasi" value="{{ old('lokasi', $project->lokasi) }}" placeholder="Jakarta Selatan">
        @error('lokasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="nilai_kontrak">Nilai Kontrak (Rp)</label>
        <input type="number" step="0.01" min="0" class="form-control @error('nilai_kontrak') is-invalid @enderror"
            id="nilai_kontrak" name="nilai_kontrak" value="{{ old('nilai_kontrak', $project->nilai_kontrak) }}"
            placeholder="45000000000">
        @error('nilai_kontrak') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="tanggal_mulai">Tanggal Mulai</label>
        <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai"
            name="tanggal_mulai" value="{{ old('tanggal_mulai', $project->tanggal_mulai?->format('Y-m-d')) }}">
        @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="tanggal_selesai">Tanggal Selesai</label>
        <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai"
            name="tanggal_selesai" value="{{ old('tanggal_selesai', $project->tanggal_selesai?->format('Y-m-d')) }}">
        @error('tanggal_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="status">Status Proyek <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $project->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="deskripsi">Deskripsi</label>
        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi"
            rows="3" placeholder="Ringkasan lingkup pekerjaan proyek">{{ old('deskripsi', $project->deskripsi) }}</textarea>
        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4">
    <a href="{{ $isEdit ? route('projects.show', $project) : route('projects.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Proyek' }}
    </button>
</div>
