<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    private function project(): Project
    {
        return Project::create([
            'kode_proyek' => 'PRJ-2021-001',
            'nama_proyek' => 'Pembangunan Gedung Uji',
            'status' => 'berjalan',
        ]);
    }

    private function documentPayload(Project $project, array $overrides = []): array
    {
        return array_merge([
            'project_id' => $project->id,
            'judul' => 'Kontrak Pembangunan Gedung Uji',
            'jenis' => 'kontrak',
            'versi' => '1.0',
            'tanggal_dokumen' => '2021-03-01',
            'valid_from' => '2021-03-01',
            'security_level' => 'rahasia',
            'lifecycle_status' => 'active',
        ], $overrides);
    }

    public function test_pm_can_create_project_and_phase(): void
    {
        $pm = $this->user(User::ROLE_PM);

        $this->actingAs($pm)->post(route('projects.store'), [
            'kode_proyek' => 'PRJ-2026-010',
            'nama_proyek' => 'Proyek Baru Uji',
            'status' => 'perencanaan',
        ])->assertRedirect();

        $project = Project::where('kode_proyek', 'PRJ-2026-010')->firstOrFail();
        $this->assertSame($pm->id, $project->created_by);

        $this->actingAs($pm)->post(route('phases.store', $project), [
            'nama' => 'Perencanaan',
            'urutan' => 1,
            'status' => 'berjalan',
        ])->assertRedirect();

        $this->assertDatabaseHas('project_phases', [
            'project_id' => $project->id,
            'nama' => 'Perencanaan',
        ]);
    }

    public function test_legal_can_archive_contract_with_automatic_retention(): void
    {
        Storage::fake('local');

        $project = $this->project();
        $legal = $this->user(User::ROLE_LEGAL);

        $this->actingAs($legal)->post(route('documents.store'), $this->documentPayload($project, [
            'file' => UploadedFile::fake()->create('kontrak.pdf', 120, 'application/pdf'),
        ]))->assertRedirect();

        $document = Document::firstOrFail();

        $this->assertSame('2031-03-01', $document->retention_date->toDateString());
        $this->assertNotNull($document->checksum_sha256);
        $this->assertSame(64, strlen($document->checksum_sha256));
        $this->assertStringContainsString('/KTR/', $document->nomor_dokumen);
        Storage::disk('local')->assertExists($document->file_path);

        $this->assertDatabaseHas('document_access_logs', [
            'document_id' => $document->id,
            'action' => 'create',
        ]);
    }

    public function test_addendum_supersedes_parent_validity(): void
    {
        $project = $this->project();
        $legal = $this->user(User::ROLE_LEGAL);

        $parent = Document::create($this->documentPayload($project, [
            'nomor_dokumen' => 'PRJ-2021-001/KTR/001/III/2021',
        ]));

        $this->assertNull($parent->valid_until);

        $this->actingAs($legal)->post(route('documents.store'), $this->documentPayload($project, [
            'parent_document_id' => $parent->id,
            'judul' => 'Addendum I',
            'jenis' => 'addendum',
            'versi' => '1.1',
            'tanggal_dokumen' => '2021-12-20',
            'valid_from' => '2022-01-01',
        ]))->assertRedirect();

        $addendum = Document::where('jenis', 'addendum')->firstOrFail();

        $this->assertSame($parent->id, $addendum->parent_document_id);
        $this->assertSame('2021-12-31', $parent->fresh()->valid_until->toDateString());
    }

    public function test_document_page_shows_version_timeline(): void
    {
        $project = $this->project();
        $legal = $this->user(User::ROLE_LEGAL);

        $parent = Document::create($this->documentPayload($project, [
            'nomor_dokumen' => 'PRJ-2021-001/KTR/001/III/2021',
        ]));

        Document::create($this->documentPayload($project, [
            'parent_document_id' => $parent->id,
            'nomor_dokumen' => 'PRJ-2021-001/ADD/001/XII/2021',
            'judul' => 'Addendum I',
            'jenis' => 'addendum',
            'versi' => '1.1',
            'tanggal_dokumen' => '2021-12-20',
            'valid_from' => '2022-01-01',
        ]));

        $this->actingAs($legal)
            ->get(route('documents.show', $parent))
            ->assertOk()
            ->assertSee('Addendum I')
            ->assertSee('Riwayat Versi');
    }

    public function test_locked_document_cannot_be_updated(): void
    {
        $project = $this->project();
        $legal = $this->user(User::ROLE_LEGAL);

        $document = Document::create($this->documentPayload($project, [
            'nomor_dokumen' => 'PRJ-2021-001/KTR/001/III/2021',
        ]));

        $this->actingAs($legal)
            ->post(route('documents.toggle-lock', $document))
            ->assertRedirect();

        $this->assertTrue($document->fresh()->is_locked);

        $this->actingAs($legal)
            ->put(route('documents.update', $document), $this->documentPayload($project, [
                'nomor_dokumen' => 'PRJ-2021-001/KTR/001/III/2021',
                'judul' => 'Judul Diubah',
            ]))
            ->assertForbidden();

        $this->assertSame('Kontrak Pembangunan Gedung Uji', $document->fresh()->judul);
        $this->assertDatabaseHas('document_access_logs', [
            'document_id' => $document->id,
            'action' => 'lock',
        ]);
    }

    public function test_download_records_access_log(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('documents/1/kontrak.pdf', 'contoh isi berkas');

        $project = $this->project();
        $document = Document::create($this->documentPayload($project, [
            'nomor_dokumen' => 'PRJ-2021-001/KTR/001/III/2021',
            'file_path' => 'documents/1/kontrak.pdf',
            'file_name' => 'kontrak.pdf',
        ]));

        $this->actingAs($this->user(User::ROLE_LEGAL))
            ->get(route('documents.download', $document))
            ->assertOk();

        $this->assertDatabaseHas('document_access_logs', [
            'document_id' => $document->id,
            'action' => 'download',
        ]);
    }

    public function test_project_with_documents_cannot_be_deleted(): void
    {
        $project = $this->project();
        $legal = $this->user(User::ROLE_LEGAL);

        Document::create($this->documentPayload($project, [
            'nomor_dokumen' => 'PRJ-2021-001/KTR/001/III/2021',
        ]));

        $this->actingAs($legal)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect();

        $this->assertNotSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_admin_can_create_classification_without_parent(): void
    {
        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->post(route('classifications.store'), [
                'kode' => 'TS-01',
                'nama' => 'Klasifikasi Uji',
                'retensi_tahun' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('classifications', [
            'kode' => 'TS-01',
            'parent_id' => null,
        ]);
    }

    public function test_admin_can_create_user(): void
    {
        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->post(route('users.store'), [
                'name' => 'Pengguna Baru',
                'email' => 'baru@earsip.test',
                'password' => 'rahasia123',
                'role' => User::ROLE_PM,
                'jabatan' => 'Project Manager',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'baru@earsip.test',
            'role' => User::ROLE_PM,
            'is_active' => true,
        ]);
    }

    public function test_contract_retention_cannot_be_shorter_than_ten_years(): void
    {
        $project = $this->project();

        $document = Document::create($this->documentPayload($project, [
            'nomor_dokumen' => 'PRJ-2021-001/KTR/001/III/2021',
            'retention_date' => '2022-01-01',
        ]));

        $this->assertSame('2031-03-01', $document->fresh()->retention_date->toDateString());
    }

    public function test_parent_document_cannot_form_a_cycle(): void
    {
        $project = $this->project();
        $legal = $this->user(User::ROLE_LEGAL);

        $parent = Document::create($this->documentPayload($project, [
            'nomor_dokumen' => 'PRJ-2021-001/KTR/001/III/2021',
        ]));

        $addendum = Document::create($this->documentPayload($project, [
            'parent_document_id' => $parent->id,
            'nomor_dokumen' => 'PRJ-2021-001/ADD/001/XII/2021',
            'jenis' => 'addendum',
            'versi' => '1.1',
        ]));

        $this->actingAs($legal)
            ->put(route('documents.update', $parent), $this->documentPayload($project, [
                'nomor_dokumen' => $parent->nomor_dokumen,
                'parent_document_id' => $addendum->id,
            ]))
            ->assertSessionHasErrors('parent_document_id');
    }

    public function test_addendum_parent_must_be_main_contract(): void
    {
        $project = $this->project();
        $legal = $this->user(User::ROLE_LEGAL);

        $contract = Document::create($this->documentPayload($project, [
            'nomor_dokumen' => 'PRJ-2021-001/KTR/001/III/2021',
        ]));

        $addendum = Document::create($this->documentPayload($project, [
            'parent_document_id' => $contract->id,
            'nomor_dokumen' => 'PRJ-2021-001/ADD/001/XII/2021',
            'jenis' => 'addendum',
            'versi' => '1.1',
        ]));

        $this->actingAs($legal)
            ->post(route('documents.store'), $this->documentPayload($project, [
                'parent_document_id' => $addendum->id,
                'judul' => 'Addendum II',
                'jenis' => 'addendum',
                'versi' => '1.2',
                'tanggal_dokumen' => '2022-01-03',
                'valid_from' => '2022-01-03',
            ]))
            ->assertSessionHasErrors('parent_document_id');
    }

    public function test_auto_number_skips_existing_manual_number(): void
    {
        $project = $this->project();
        $legal = $this->user(User::ROLE_LEGAL);

        Document::create($this->documentPayload($project, [
            'nomor_dokumen' => 'PRJ-2021-001/KTR/002/III/2021',
            'judul' => 'Kontrak Manual',
        ]));

        $this->actingAs($legal)
            ->post(route('documents.store'), $this->documentPayload($project, [
                'judul' => 'Kontrak Otomatis',
            ]))
            ->assertRedirect();

        $document = Document::where('judul', 'Kontrak Otomatis')->firstOrFail();

        $this->assertSame('PRJ-2021-001/KTR/003/III/2021', $document->nomor_dokumen);
    }
}
