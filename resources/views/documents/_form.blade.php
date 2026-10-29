@php
    $isEdit = $document->exists;
    $phaseMap = $projects->mapWithKeys(fn ($project) => [
        $project->id => $project->phases->map(fn ($phase) => ['id' => $phase->id, 'nama' => $phase->nama])->values(),
    ]);
    $selectedProject = old('project_id', $document->project_id);
    $selectedPhase = old('project_phase_id', $document->project_phase_id);
    $selectedParent = old('parent_document_id', $document->parent_document_id);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="project_id">Proyek <span class="text-danger">*</span></label>
        <select class="form-select @error('project_id') is-invalid @enderror" id="project_id" name="project_id" required>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}" @selected((string) $selectedProject === (string) $project->id)>
                    {{ $project->kode_proyek }} — {{ $project->nama_proyek }}
                </option>
            @endforeach
        </select>
        @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="project_phase_id">Fase / Milestone</label>
        <select class="form-select @error('project_phase_id') is-invalid @enderror" id="project_phase_id" name="project_phase_id">
            <option value="">— Tidak terhubung fase —</option>
        </select>
        <div class="form-text">Menghubungkan dokumen dengan linimasa proyek (FR-06).</div>
        @error('project_phase_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="jenis">Jenis Dokumen <span class="text-danger">*</span></label>
        <select class="form-select @error('jenis') is-invalid @enderror" id="jenis" name="jenis" required>
            @foreach ($jenisList as $value => $label)
                <option value="{{ $value }}" @selected(old('jenis', $document->jenis) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="parent_document_id">Dokumen Induk</label>
        <select class="form-select @error('parent_document_id') is-invalid @enderror" id="parent_document_id" name="parent_document_id">
            <option value="">— Dokumen utama (tanpa induk) —</option>
        </select>
        <div class="form-text">Untuk addendum, pilih kontrak utama (bukan addendum lain).</div>
        @error('parent_document_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-8">
        <label class="form-label" for="judul">Judul Dokumen <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul" name="judul"
            value="{{ old('judul', $document->judul) }}" placeholder="Kontrak Pembangunan Gedung Kantor Regional" required>
        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="versi">Versi <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('versi') is-invalid @enderror" id="versi" name="versi"
            value="{{ old('versi', $document->versi ?? '1.0') }}" placeholder="1.0" required>
        @error('versi') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="nomor_dokumen">Nomor Dokumen</label>
        <input type="text" class="form-control @error('nomor_dokumen') is-invalid @enderror" id="nomor_dokumen"
            name="nomor_dokumen" value="{{ old('nomor_dokumen', $document->nomor_dokumen) }}"
            placeholder="Otomatis jika dikosongkan">
        @error('nomor_dokumen') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">Format otomatis mengikuti kode proyek/jenis/bulan/tahun.</div>
    </div>

    <div class="col-md-4">
        <label class="form-label" for="classification_id">Klasifikasi Arsip</label>
        <select class="form-select @error('classification_id') is-invalid @enderror" id="classification_id" name="classification_id">
            <option value="">— Tidak diklasifikasikan —</option>
            @foreach ($classifications as $classification)
                <option value="{{ $classification->id }}" @selected((string) old('classification_id', $document->classification_id) === (string) $classification->id)>
                    {{ $classification->kode }} — {{ $classification->nama }} ({{ $classification->retensi_tahun }} th)
                </option>
            @endforeach
        </select>
        @error('classification_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="security_level">Tingkat Keamanan <span class="text-danger">*</span></label>
        <select class="form-select @error('security_level') is-invalid @enderror" id="security_level" name="security_level" required>
            @foreach ($securityList as $value => $label)
                <option value="{{ $value }}" @selected(old('security_level', $document->security_level) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="form-text">Mengacu pada SKKAAD (Biasa s.d. Sangat Rahasia).</div>
        @error('security_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="lifecycle_status">Status Siklus Hidup <span class="text-danger">*</span></label>
        <select class="form-select @error('lifecycle_status') is-invalid @enderror" id="lifecycle_status" name="lifecycle_status" required>
            @foreach ($lifecycleList as $value => $label)
                <option value="{{ $value }}" @selected(old('lifecycle_status', $document->lifecycle_status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('lifecycle_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="tanggal_dokumen">Tanggal Dokumen <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('tanggal_dokumen') is-invalid @enderror" id="tanggal_dokumen"
            name="tanggal_dokumen" value="{{ old('tanggal_dokumen', $document->tanggal_dokumen?->format('Y-m-d')) }}" required>
        @error('tanggal_dokumen') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="valid_from">Berlaku Sejak <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('valid_from') is-invalid @enderror" id="valid_from"
            name="valid_from" value="{{ old('valid_from', $document->valid_from?->format('Y-m-d')) }}" required>
        @error('valid_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="valid_until">Berlaku Sampai</label>
        <input type="date" class="form-control @error('valid_until') is-invalid @enderror" id="valid_until"
            name="valid_until" value="{{ old('valid_until', $document->valid_until?->format('Y-m-d')) }}">
        <div class="form-text">Kosongkan bila masih berlaku.</div>
        @error('valid_until') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label" for="retention_date">Tanggal Retensi</label>
        <input type="date" class="form-control @error('retention_date') is-invalid @enderror" id="retention_date"
            name="retention_date" value="{{ old('retention_date', $document->retention_date?->format('Y-m-d')) }}">
        <div class="form-text">Kosongkan untuk otomatis: tanggal dokumen + minimal 10 tahun.</div>
        @error('retention_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="deskripsi">Deskripsi / Ringkasan Isi</label>
        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi"
            rows="3" placeholder="Ringkasan isi dokumen, pihak terkait, atau catatan penting">{{ old('deskripsi', $document->deskripsi) }}</textarea>
        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label" for="file">Berkas Dokumen</label>
        <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file"
            accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
        <div class="form-text">
            PDF, Word, Excel, atau gambar. Maksimal 10 MB. Berkas disimpan privat dan hanya dapat diunduh sesuai hak akses.
        </div>
        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror

        @if ($isEdit && $document->file_path)
            <div class="mt-2 small text-secondary">
                <i class="bi bi-paperclip me-1"></i> Berkas saat ini:
                <span class="code-chip">{{ $document->file_name }}</span> ({{ $document->fileSizeLabel() }})
                <span class="d-block mt-1">Mengunggah berkas baru akan menggantikan berkas lama pada versi ini.</span>
            </div>
        @endif
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4">
    <a href="{{ $isEdit ? route('documents.show', $document) : route('documents.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Arsipkan Dokumen' }}
    </button>
</div>

@push('scripts')
    <script>
        const phaseMap = @json($phaseMap);
        const parentOptions = @json($parentOptions);
        const initialPhase = @json($selectedPhase);
        const initialParent = @json($selectedParent);

        function fillPhases(projectId, selected) {
            const select = document.getElementById('project_phase_id');
            const phases = phaseMap[String(projectId)] || [];
            select.innerHTML = '<option value="">— Tidak terhubung fase —</option>';
            phases.forEach(function (phase) {
                const option = document.createElement('option');
                option.value = phase.id;
                option.textContent = phase.nama;
                if (String(phase.id) === String(selected)) option.selected = true;
                select.appendChild(option);
            });
        }

        function fillParents(projectId, selected) {
            const select = document.getElementById('parent_document_id');
            const jenis = document.getElementById('jenis').value;
            const allowedJenis = jenis === 'addendum' ? ['kontrak'] : ['kontrak', 'addendum'];
            select.innerHTML = '<option value="">— Dokumen utama (tanpa induk) —</option>';
            parentOptions
                .filter(function (doc) {
                    return String(doc.project_id) === String(projectId)
                        && allowedJenis.indexOf(doc.jenis) !== -1;
                })
                .forEach(function (doc) {
                    const option = document.createElement('option');
                    option.value = doc.id;
                    option.textContent = doc.nomor_dokumen + ' — ' + doc.judul + ' (v' + doc.versi + ')';
                    if (String(doc.id) === String(selected)) option.selected = true;
                    select.appendChild(option);
                });
        }

        document.getElementById('project_id').addEventListener('change', function () {
            fillPhases(this.value, null);
            fillParents(this.value, null);
        });

        document.getElementById('jenis').addEventListener('change', function () {
            fillParents(document.getElementById('project_id').value, null);
        });

        fillPhases(document.getElementById('project_id').value, initialPhase);
        fillParents(document.getElementById('project_id').value, initialParent);
    </script>
@endpush
