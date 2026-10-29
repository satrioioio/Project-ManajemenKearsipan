@php
    $isEdit = $classification->exists;
@endphp

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label" for="kode">Kode <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('kode') is-invalid @enderror" id="kode" name="kode"
            value="{{ old('kode', $classification->kode) }}" placeholder="KP-02" required>
        @error('kode') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-9">
        <label class="form-label" for="nama">Nama Klasifikasi <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama"
            value="{{ old('nama', $classification->nama) }}" placeholder="Addendum Kontrak" required>
        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="parent_id">Klasifikasi Induk</label>
        <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
            <option value="">— Tidak ada (klasifikasi utama) —</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $classification->parent_id) === (string) $parent->id)>
                    {{ $parent->kode }} — {{ $parent->nama }}
                </option>
            @endforeach
        </select>
        @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="retensi_tahun">Retensi (tahun) <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('retensi_tahun') is-invalid @enderror" id="retensi_tahun"
            name="retensi_tahun" value="{{ old('retensi_tahun', $classification->retensi_tahun) }}" min="1" max="100" required>
        <div class="form-text">Kontrak dan addendum otomatis minimal 10 tahun (UU No. 2/2017).</div>
        @error('retensi_tahun') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="keterangan">Keterangan</label>
        <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan"
            rows="3" placeholder="Penjelasan cakupan klasifikasi ini">{{ old('keterangan', $classification->keterangan) }}</textarea>
        @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4">
    <a href="{{ route('classifications.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Klasifikasi' }}
    </button>
</div>
