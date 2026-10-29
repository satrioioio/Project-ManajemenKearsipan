@php
    $isEdit = $user->exists;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="name">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
            value="{{ old('name', $user->name) }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
            value="{{ old('email', $user->email) }}" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="password">Kata Sandi {{ $isEdit ? '' : '*' }}</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password"
            {{ $isEdit ? '' : 'required' }} minlength="8" autocomplete="new-password">
        <div class="form-text">{{ $isEdit ? 'Kosongkan bila tidak ingin mengubah kata sandi.' : 'Minimal 8 karakter.' }}</div>
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label" for="role">Peran <span class="text-danger">*</span></label>
        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="form-text">Legal &amp; PM dapat mengelola dokumen; Staf hanya melihat arsip terbuka.</div>
        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-8">
        <label class="form-label" for="jabatan">Jabatan</label>
        <input type="text" class="form-control @error('jabatan') is-invalid @enderror" id="jabatan" name="jabatan"
            value="{{ old('jabatan', $user->jabatan) }}" placeholder="Staf Legal / Project Manager">
        @error('jabatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                @checked(old('is_active', $user->is_active ?? true))>
            <label class="form-check-label" for="is_active">Akun aktif</label>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4">
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
    </button>
</div>
