<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function project(): Project
    {
        return Project::create([
            'kode_proyek' => 'PRJ-TEST-001',
            'nama_proyek' => 'Proyek Uji',
            'status' => 'berjalan',
        ]);
    }

    private function document(array $overrides = []): Document
    {
        $project = $this->project();

        return Document::create(array_merge([
            'project_id' => $project->id,
            'nomor_dokumen' => 'PRJ-TEST-001/KTR/001/X/2026',
            'judul' => 'Kontrak Uji',
            'jenis' => 'kontrak',
            'versi' => '1.0',
            'tanggal_dokumen' => '2026-01-01',
            'valid_from' => '2026-01-01',
            'lifecycle_status' => 'active',
            'security_level' => 'rahasia',
        ], $overrides));
    }

    public function test_guest_redirected_to_login(): void
    {
        $this->get('/dokumen')->assertRedirect(route('login'));
    }

    public function test_legal_can_view_contract_and_access_is_logged(): void
    {
        $document = $this->document();

        $this->actingAs($this->user(User::ROLE_LEGAL))
            ->get(route('documents.show', $document))
            ->assertOk();

        $this->assertDatabaseHas('document_access_logs', [
            'document_id' => $document->id,
            'action' => 'view',
        ]);
    }

    public function test_staff_cannot_view_contract_and_denial_is_logged(): void
    {
        $document = $this->document();

        $this->actingAs($this->user(User::ROLE_STAFF))
            ->get(route('documents.show', $document))
            ->assertForbidden();

        $this->assertDatabaseHas('document_access_logs', [
            'document_id' => $document->id,
            'action' => 'denied',
        ]);
    }

    public function test_staff_can_view_open_bast_document(): void
    {
        $document = $this->document([
            'nomor_dokumen' => 'PRJ-TEST-001/BAST/001/X/2026',
            'judul' => 'BAST Uji',
            'jenis' => 'bast',
            'security_level' => 'biasa',
        ]);

        $this->actingAs($this->user(User::ROLE_STAFF))
            ->get(route('documents.show', $document))
            ->assertOk();
    }

    public function test_pm_cannot_view_very_secret_document(): void
    {
        $document = $this->document(['security_level' => 'sangat_rahasia']);

        $this->actingAs($this->user(User::ROLE_PM))
            ->get(route('documents.show', $document))
            ->assertForbidden();
    }

    public function test_pm_can_view_contract(): void
    {
        $document = $this->document();

        $this->actingAs($this->user(User::ROLE_PM))
            ->get(route('documents.show', $document))
            ->assertOk();
    }

    public function test_staff_cannot_open_management_pages(): void
    {
        $staff = $this->user(User::ROLE_STAFF);

        $this->actingAs($staff)->get(route('documents.create'))->assertForbidden();
        $this->actingAs($staff)->get(route('projects.create'))->assertForbidden();
        $this->actingAs($staff)->get(route('classifications.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('users.index'))->assertForbidden();
    }

    public function test_pm_cannot_manage_users_or_classifications(): void
    {
        $pm = $this->user(User::ROLE_PM);

        $this->actingAs($pm)->get(route('users.index'))->assertForbidden();
        $this->actingAs($pm)->get(route('classifications.index'))->assertForbidden();
    }

    public function test_staff_cannot_download_contract(): void
    {
        $document = $this->document(['file_path' => 'documents/1/kontrak.pdf']);

        $this->actingAs($this->user(User::ROLE_STAFF))
            ->get(route('documents.download', $document))
            ->assertForbidden();
    }
}
