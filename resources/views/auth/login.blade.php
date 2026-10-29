<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="login-page">
        <section class="login-visual">
            <a class="brand" href="{{ route('login') }}">
                <span class="brand-mark"><i class="bi bi-archive-fill"></i></span>
                <span>
                    <strong>e-Arsip Kontrak</strong>
                    <small>Divisi Kontrak Perusahaan Konstruksi</small>
                </span>
            </a>

            <div>
                <span class="badge bg-primary bg-opacity-25 text-info mb-3 px-3 py-2">
                    <i class="bi bi-shield-lock me-1"></i> Sistem Manajemen Arsip Elektronik
                </span>
                <h1>Satu sumber kebenaran untuk seluruh kontrak dan addendum proyek.</h1>
                <p>
                    Kelola dokumen kontrak, addendum, dan dokumen pendukung dalam satu tempat.
                    Setiap versi tercatat masa berlakunya, akses dibatasi sesuai peran, dan
                    retensi arsip dijaga minimal 10 tahun.
                </p>
                <div class="login-points">
                    <span><i class="bi bi-check-circle-fill"></i> Versi &amp; addendum</span>
                    <span><i class="bi bi-check-circle-fill"></i> RBAC Legal / PM / Staf</span>
                    <span><i class="bi bi-check-circle-fill"></i> Retensi 10 tahun</span>
                    <span><i class="bi bi-check-circle-fill"></i> Jejak akses</span>
                </div>
            </div>

            <div class="small text-secondary">
                &copy; {{ date('Y') }} {{ config('app.name') }} — Proyek Akhir Manajemen Kearsipan
            </div>
        </section>

        <section class="login-form-side">
            <div class="login-card">
                <h2 class="h4 fw-bold mb-1">Masuk ke sistem</h2>
                <p class="text-secondary mb-4 small">Gunakan akun yang telah terdaftar oleh administrator.</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                            id="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="nama@perusahaan.co.id">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Kata Sandi</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            id="password" name="password" required placeholder="••••••••">
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small" for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                    </button>
                </form>

                <div class="mt-4 p-3 rounded-3 bg-light border">
                    <div class="fw-semibold small mb-2">Akun demo</div>
                    <div class="small text-secondary lh-lg">
                        <div><span class="code-chip">admin@earsip.test</span> — Administrator</div>
                        <div><span class="code-chip">legal@earsip.test</span> — Legal</div>
                        <div><span class="code-chip">pm@earsip.test</span> — Project Manager</div>
                        <div><span class="code-chip">staf@earsip.test</span> — Staf</div>
                        <div class="mt-1">Kata sandi seluruh akun: <span class="code-chip">password</span></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
