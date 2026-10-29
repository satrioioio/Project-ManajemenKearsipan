# e-Arsip Kontrak

Sistem manajemen arsip elektronik untuk divisi kontrak perusahaan konstruksi. Aplikasi ini menyimpan kontrak, addendum, dan dokumen pendukung dalam satu tempat, mencatat masa berlaku setiap versi, membatasi akses berdasarkan peran, serta menghitung retensi arsip minimal 10 tahun.

## Fitur

- Manajemen proyek konstruksi dan fase/milestone.
- Unggah kontrak utama, addendum, BAST, izin, dan dokumen pendukung.
- Riwayat versi dengan masa berlaku (`valid_from`, `valid_until`).
- Addendum terhubung ke kontrak utama dan menutup masa berlaku versi sebelumnya.
- Retensi otomatis minimal 10 tahun untuk kontrak dan addendum.
- Klasifikasi arsip berjenjang beserta jadwal retensi.
- Hak akses berdasarkan peran dan tingkat keamanan dokumen.
- Jejak akses: lihat, unduh, ubah, hapus, kunci, dan akses ditolak.
- Kunci arsip agar dokumen menjadi read-only.
- Checksum SHA-256 untuk memeriksa integritas berkas.
- Pencarian dan filter dokumen.
- Dashboard ringkasan proyek dan arsip.

## Peran Pengguna

| Peran | Hak akses utama |
|-------|-----------------|
| Administrator | Seluruh data, pengguna, dan klasifikasi |
| Legal | Seluruh dokumen, unggah/ubah/hapus, kunci arsip |
| Project Manager | Proyek/fase, unggah dokumen, ubah dokumen miliknya |
| Staf | Melihat proyek dan dokumen berlevel biasa yang bukan kontrak/addendum |

## Teknologi

- PHP 8.2+ dan Laravel 12
- SQLite (default) atau MySQL
- Blade dan Bootstrap 5
- PHPUnit 11

Tidak memerlukan Node.js atau proses build frontend.

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Buat file database SQLite (Windows):

```powershell
New-Item -ItemType File database\database.sqlite -Force
```

Linux/macOS:

```bash
touch database/database.sqlite
```

Jalankan migrasi dan data demo:

```bash
php artisan migrate --seed
php artisan serve
```

Buka `http://127.0.0.1:8000`.

### Menggunakan MySQL

Ubah bagian database pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=earsip
DB_USERNAME=root
DB_PASSWORD=
```

Lalu jalankan `php artisan migrate:fresh --seed`.

## Akun Demo

| Peran | Email | Kata sandi |
|-------|-------|------------|
| Administrator | admin@earsip.test | password |
| Legal | legal@earsip.test | password |
| Project Manager | pm@earsip.test | password |
| Staf | staf@earsip.test | password |

## Pengujian

```bash
php artisan test
```

## Struktur Direktori

```text
app/Http/Controllers   Controller aplikasi
app/Models             Model Eloquent
app/Policies           Aturan akses dokumen
database/migrations    Skema database
database/seeders       Data demo
resources/views        Tampilan Blade
routes/web.php         Routing aplikasi
tests/Feature          Pengujian fitur
```

## Catatan

Repositori ini hanya memuat aplikasi. Dokumen perancangan (ERD/UML), laporan sprint, dan panduan penggunaan disiapkan terpisah sebagai lampiran akademik.

Dokumen demo tidak menyertakan berkas fisik. Untuk menguji unduhan dan checksum, unggah berkas sendiri melalui menu Dokumen.

Fitur disposisi arsip dan halaman audit trail terpusat belum termasuk dalam versi ini.
