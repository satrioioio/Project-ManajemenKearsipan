<?php

namespace Database\Seeders;

use App\Models\Classification;
use App\Models\Document;
use App\Models\DocumentAccessLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator Sistem',
            'email' => 'admin@earsip.test',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'jabatan' => 'Administrator Arsip',
            'is_active' => true,
        ]);

        $legal = User::create([
            'name' => 'Rina Kartika',
            'email' => 'legal@earsip.test',
            'password' => 'password',
            'role' => User::ROLE_LEGAL,
            'jabatan' => 'Staf Divisi Legal',
            'is_active' => true,
        ]);

        $pm = User::create([
            'name' => 'Bagus Pratama',
            'email' => 'pm@earsip.test',
            'password' => 'password',
            'role' => User::ROLE_PM,
            'jabatan' => 'Project Manager',
            'is_active' => true,
        ]);

        $staff = User::create([
            'name' => 'Dewi Lestari',
            'email' => 'staf@earsip.test',
            'password' => 'password',
            'role' => User::ROLE_STAFF,
            'jabatan' => 'Staf Administrasi Umum',
            'is_active' => true,
        ]);

        $kontrak = Classification::create([
            'kode' => 'KP',
            'nama' => 'Kontrak & Perjanjian',
            'retensi_tahun' => 10,
            'keterangan' => 'Seluruh dokumen kontrak dan perjanjian dengan pihak ketiga.',
        ]);
        $proyek = Classification::create([
            'kode' => 'PR',
            'nama' => 'Proyek & Pekerjaan',
            'retensi_tahun' => 10,
            'keterangan' => 'Dokumen pelaksanaan, perizinan, dan serah terima proyek.',
        ]);
        $umum = Classification::create([
            'kode' => 'UM',
            'nama' => 'Umum & Korespondensi',
            'retensi_tahun' => 5,
            'keterangan' => 'Surat masuk dan surat keluar serta dokumen umum.',
        ]);

        $kp01 = Classification::create([
            'kode' => 'KP-01', 'nama' => 'Kontrak Utama', 'parent_id' => $kontrak->id, 'retensi_tahun' => 10,
        ]);
        $kp02 = Classification::create([
            'kode' => 'KP-02', 'nama' => 'Addendum Kontrak', 'parent_id' => $kontrak->id, 'retensi_tahun' => 10,
        ]);
        $pr01 = Classification::create([
            'kode' => 'PR-01', 'nama' => 'Berita Acara Serah Terima', 'parent_id' => $proyek->id, 'retensi_tahun' => 10,
        ]);
        $pr02 = Classification::create([
            'kode' => 'PR-02', 'nama' => 'Dokumen Perizinan', 'parent_id' => $proyek->id, 'retensi_tahun' => 10,
        ]);
        Classification::create([
            'kode' => 'UM-01', 'nama' => 'Surat Masuk', 'parent_id' => $umum->id, 'retensi_tahun' => 5,
        ]);
        Classification::create([
            'kode' => 'UM-02', 'nama' => 'Surat Keluar', 'parent_id' => $umum->id, 'retensi_tahun' => 5,
        ]);

        $proyekSelesai = Project::create([
            'kode_proyek' => 'PRJ-2021-001',
            'nama_proyek' => 'Pembangunan Gedung Kantor Regional',
            'pemilik_pekerjaan' => 'PT Nusantara Energi',
            'lokasi' => 'Jakarta Selatan',
            'nilai_kontrak' => 45000000000,
            'tanggal_mulai' => '2021-03-01',
            'tanggal_selesai' => '2023-08-30',
            'status' => 'selesai',
            'deskripsi' => 'Pembangunan gedung kantor 6 lantai beserta pekerjaan struktur, MEP, dan finishing. Proyek telah selesai dan saat ini memasuki masa sengketa atas dugaan cacat struktural.',
            'created_by' => $pm->id,
        ]);

        $fase1 = $proyekSelesai->phases()->create([
            'nama' => 'Perencanaan & Perizinan', 'urutan' => 1,
            'tanggal_mulai' => '2021-03-01', 'tanggal_selesai' => '2021-06-30',
            'status' => 'selesai', 'deskripsi' => 'Perencanaan teknis dan pengurusan izin mendirikan bangunan.',
        ]);
        $fase2 = $proyekSelesai->phases()->create([
            'nama' => 'Pelaksanaan Struktur', 'urutan' => 2,
            'tanggal_mulai' => '2021-07-01', 'tanggal_selesai' => '2022-06-30',
            'status' => 'selesai', 'deskripsi' => 'Pekerjaan pondasi, kolom, balok, dan plat lantai.',
        ]);
        $fase3 = $proyekSelesai->phases()->create([
            'nama' => 'Finishing & MEP', 'urutan' => 3,
            'tanggal_mulai' => '2022-07-01', 'tanggal_selesai' => '2023-05-31',
            'status' => 'selesai', 'deskripsi' => 'Pekerjaan arsitektur, mekanikal, elektrikal, dan plumbing.',
        ]);
        $fase4 = $proyekSelesai->phases()->create([
            'nama' => 'Serah Terima & Masa Pemeliharaan', 'urutan' => 4,
            'tanggal_mulai' => '2023-06-01', 'tanggal_selesai' => '2023-08-30',
            'status' => 'selesai', 'deskripsi' => 'Berita acara serah terima dan masa pemeliharaan.',
        ]);

        $proyekBerjalan = Project::create([
            'kode_proyek' => 'PRJ-2024-002',
            'nama_proyek' => 'Revitalisasi Gudang Logistik',
            'pemilik_pekerjaan' => 'PT Sentosa Niaga',
            'lokasi' => 'Bekasi',
            'nilai_kontrak' => 12500000000,
            'tanggal_mulai' => '2024-02-01',
            'tanggal_selesai' => '2025-12-31',
            'status' => 'berjalan',
            'deskripsi' => 'Renovasi gudang logistik dan penambahan area bongkar muat.',
            'created_by' => $pm->id,
        ]);

        $proyekBerjalan->phases()->create([
            'nama' => 'Perencanaan', 'urutan' => 1,
            'tanggal_mulai' => '2024-02-01', 'tanggal_selesai' => '2024-04-30', 'status' => 'selesai',
        ]);
        $proyekBerjalan->phases()->create([
            'nama' => 'Pelaksanaan', 'urutan' => 2,
            'tanggal_mulai' => '2024-05-01', 'tanggal_selesai' => '2025-08-31', 'status' => 'berjalan',
        ]);
        $proyekBerjalan->phases()->create([
            'nama' => 'Serah Terima', 'urutan' => 3,
            'tanggal_mulai' => '2025-09-01', 'tanggal_selesai' => '2025-12-31', 'status' => 'pending',
        ]);

        $kontrakUtama = Document::create([
            'project_id' => $proyekSelesai->id,
            'project_phase_id' => $fase1->id,
            'classification_id' => $kp01->id,
            'nomor_dokumen' => 'PRJ-2021-001/KTR/001/III/2021',
            'judul' => 'Kontrak Pembangunan Gedung Kantor Regional',
            'jenis' => 'kontrak',
            'versi' => '1.0',
            'deskripsi' => 'Kontrak induk beserta syarat umum dan khusus, lingkup pekerjaan, dan jadwal pelaksanaan.',
            'tanggal_dokumen' => '2021-03-01',
            'valid_from' => '2021-03-01',
            'valid_until' => '2021-12-31',
            'security_level' => 'rahasia',
            'created_by' => $legal->id,
        ]);

        $addendum1 = Document::create([
            'project_id' => $proyekSelesai->id,
            'project_phase_id' => $fase2->id,
            'classification_id' => $kp02->id,
            'parent_document_id' => $kontrakUtama->id,
            'nomor_dokumen' => 'PRJ-2021-001/ADD/001/XII/2021',
            'judul' => 'Addendum I — Penyesuaian Lingkup Pekerjaan Struktur',
            'jenis' => 'addendum',
            'versi' => '1.1',
            'deskripsi' => 'Penyesuaian volume pekerjaan struktur lantai 5-6 dan perpanjangan waktu 3 bulan.',
            'tanggal_dokumen' => '2021-12-20',
            'valid_from' => '2022-01-01',
            'valid_until' => '2022-12-31',
            'security_level' => 'rahasia',
            'created_by' => $legal->id,
        ]);

        Document::create([
            'project_id' => $proyekSelesai->id,
            'project_phase_id' => $fase3->id,
            'classification_id' => $kp02->id,
            'parent_document_id' => $kontrakUtama->id,
            'nomor_dokumen' => 'PRJ-2021-001/ADD/002/XII/2022',
            'judul' => 'Addendum II — Perpanjangan Waktu dan Penyesuaian Biaya',
            'jenis' => 'addendum',
            'versi' => '1.2',
            'deskripsi' => 'Perpanjangan waktu pelaksanaan sampai 30 Agustus 2023 dan penyesuaian biaya akibat perubahan lingkup.',
            'tanggal_dokumen' => '2022-12-15',
            'valid_from' => '2023-01-01',
            'valid_until' => null,
            'security_level' => 'rahasia',
            'created_by' => $legal->id,
        ]);

        Document::create([
            'project_id' => $proyekSelesai->id,
            'project_phase_id' => $fase4->id,
            'classification_id' => $pr01->id,
            'nomor_dokumen' => 'PRJ-2021-001/BAST/001/VIII/2023',
            'judul' => 'Berita Acara Serah Terima Pekerjaan',
            'jenis' => 'bast',
            'versi' => '1.0',
            'deskripsi' => 'Berita acara serah terima pekerjaan dan awal masa pemeliharaan.',
            'tanggal_dokumen' => '2023-08-30',
            'valid_from' => '2023-08-30',
            'valid_until' => null,
            'security_level' => 'biasa',
            'created_by' => $pm->id,
        ]);

        Document::create([
            'project_id' => $proyekSelesai->id,
            'project_phase_id' => $fase1->id,
            'classification_id' => $pr02->id,
            'nomor_dokumen' => 'PRJ-2021-001/IZN/001/II/2021',
            'judul' => 'Izin Mendirikan Bangunan (IMB)',
            'jenis' => 'izin',
            'versi' => '1.0',
            'deskripsi' => 'Izin mendirikan bangunan untuk Gedung Kantor Regional.',
            'tanggal_dokumen' => '2021-02-10',
            'valid_from' => '2021-02-10',
            'valid_until' => null,
            'security_level' => 'biasa',
            'created_by' => $pm->id,
        ]);

        Document::create([
            'project_id' => $proyekBerjalan->id,
            'project_phase_id' => $proyekBerjalan->phases()->first()->id,
            'classification_id' => $kp01->id,
            'nomor_dokumen' => 'PRJ-2024-002/KTR/001/II/2024',
            'judul' => 'Kontrak Revitalisasi Gudang Logistik',
            'jenis' => 'kontrak',
            'versi' => '1.0',
            'deskripsi' => 'Kontrak pelaksanaan revitalisasi gudang dan area bongkar muat.',
            'tanggal_dokumen' => '2024-02-01',
            'valid_from' => '2024-02-01',
            'valid_until' => null,
            'security_level' => 'terbatas',
            'created_by' => $pm->id,
        ]);

        $this->seedAccessLogs($kontrakUtama, $addendum1, $legal, $pm, $staff);
    }

    private function seedAccessLogs(Document $kontrak, Document $addendum, User $legal, User $pm, User $staff): void
    {
        DocumentAccessLog::create([
            'document_id' => $kontrak->id,
            'user_id' => $legal->id,
            'action' => 'view',
            'ip_address' => '10.10.1.12',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'created_at' => now()->subDays(12)->setTime(9, 14),
        ]);

        DocumentAccessLog::create([
            'document_id' => $kontrak->id,
            'user_id' => $pm->id,
            'action' => 'download',
            'ip_address' => '10.10.1.31',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'created_at' => now()->subDays(5)->setTime(14, 2),
        ]);

        DocumentAccessLog::create([
            'document_id' => $addendum->id,
            'user_id' => $legal->id,
            'action' => 'view',
            'ip_address' => '10.10.1.12',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'created_at' => now()->subDays(3)->setTime(10, 41),
        ]);

        DocumentAccessLog::create([
            'document_id' => $kontrak->id,
            'user_id' => $staff->id,
            'action' => 'denied',
            'ip_address' => '10.10.1.55',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'created_at' => now()->subDays(2)->setTime(16, 20),
        ]);
    }
}
