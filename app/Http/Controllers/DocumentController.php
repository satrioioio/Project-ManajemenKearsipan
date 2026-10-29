<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use App\Models\Document;
use App\Models\DocumentAccessLog;
use App\Models\Project;
use App\Models\ProjectPhase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::query()
            ->visibleTo($request->user())
            ->search($request->string('q')->toString())
            ->when($request->filled('project'), fn ($query) => $query->where('project_id', $request->integer('project')))
            ->when($request->filled('jenis'), fn ($query) => $query->where('jenis', $request->string('jenis')))
            ->when($request->filled('security'), fn ($query) => $query->where('security_level', $request->string('security')))
            ->with(['project', 'phase', 'classification', 'parent'])
            ->latest('tanggal_dokumen')
            ->paginate(10)
            ->withQueryString();

        return view('documents.index', [
            'documents' => $documents,
            'projects' => Project::orderBy('nama_proyek')->get(),
            'jenisList' => Document::JENIS,
            'securityList' => Document::SECURITY_LEVELS,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Document::class);

        $projects = Project::with('phases')->orderBy('nama_proyek')->get();
        $selectedProjectId = $request->integer('project') ?: $projects->first()?->id;

        return view('documents.create', [
            'document' => new Document([
                'project_id' => $selectedProjectId,
                'jenis' => 'kontrak',
                'versi' => '1.0',
                'tanggal_dokumen' => now()->toDateString(),
                'valid_from' => now()->toDateString(),
                'security_level' => 'terbatas',
                'lifecycle_status' => 'active',
            ]),
            'projects' => $projects,
            'classifications' => Classification::orderBy('kode')->get(),
            'parentOptions' => $this->parentOptions(),
            'jenisList' => Document::JENIS,
            'securityList' => Document::SECURITY_LEVELS,
            'lifecycleList' => Document::LIFECYCLE_STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Document::class);

        $data = $this->validated($request);
        $project = Project::findOrFail($data['project_id']);

        $this->assertRelations($data, $project);

        if (blank($data['nomor_dokumen'] ?? null)) {
            $data['nomor_dokumen'] = $this->generateNomor(
                $project,
                $data['jenis'],
                Carbon::parse($data['tanggal_dokumen'])
            );
        }

        if ($request->hasFile('file')) {
            $data = array_merge($data, $this->storeUploadedFile($request->file('file'), $project));
        }

        unset($data['file']);
        $data['created_by'] = $request->user()->id;

        $document = Document::create($data);
        $this->supersedeParent($document);
        $this->log($document, 'create');

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Dokumen berhasil diarsipkan.');
    }

    public function show(Request $request, Document $document)
    {
        $this->authorizeDocument('view', $document);

        $document->load(['project', 'phase', 'classification', 'parent', 'creator', 'children.classification']);
        $this->log($document, 'view');

        $timeline = $this->relatedTimeline($document);
        $logs = $document->accessLogs()->with('user')->limit(20)->get();

        return view('documents.show', compact('document', 'timeline', 'logs'));
    }

    public function edit(Document $document)
    {
        $this->authorizeDocument('update', $document);

        return view('documents.edit', [
            'document' => $document->load(['project', 'phase']),
            'projects' => Project::with('phases')->orderBy('nama_proyek')->get(),
            'classifications' => Classification::orderBy('kode')->get(),
            'parentOptions' => $this->parentOptions($document->id),
            'jenisList' => Document::JENIS,
            'securityList' => Document::SECURITY_LEVELS,
            'lifecycleList' => Document::LIFECYCLE_STATUSES,
        ]);
    }

    public function update(Request $request, Document $document)
    {
        $this->authorizeDocument('update', $document);

        $data = $this->validated($request, $document);
        $project = Project::findOrFail($data['project_id']);

        $this->assertRelations($data, $project, $document);

        if (blank($data['nomor_dokumen'] ?? null)) {
            $data['nomor_dokumen'] = $this->generateNomor(
                $project,
                $data['jenis'],
                Carbon::parse($data['tanggal_dokumen']),
                $document->id
            );
        }

        if ($request->hasFile('file')) {
            if ($document->file_path) {
                Storage::disk('local')->delete($document->file_path);
            }

            $data = array_merge($data, $this->storeUploadedFile($request->file('file'), $project));
        }

        unset($data['file']);

        if (blank($data['retention_date'] ?? null)) {
            $data['retention_date'] = null;
        }

        $document->update($data);
        $this->log($document, 'update');

        return redirect()
            ->route('documents.show', $document)
            ->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Document $document)
    {
        $this->authorizeDocument('delete', $document);

        $this->log($document, 'delete');
        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('success', 'Dokumen berhasil dihapus dari daftar aktif.');
    }

    public function download(Request $request, Document $document)
    {
        $this->authorizeDocument('download', $document);

        if (! $document->file_path || ! Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'Berkas dokumen tidak ditemukan pada penyimpanan.');
        }

        $this->log($document, 'download');

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function toggleLock(Request $request, Document $document)
    {
        $this->authorizeDocument('lock', $document);

        $document->update(['is_locked' => ! $document->is_locked]);
        $this->log($document, $document->is_locked ? 'lock' : 'unlock');

        return back()->with(
            'success',
            $document->is_locked
                ? 'Dokumen dikunci sebagai arsip resmi (read-only).'
                : 'Kunci arsip dibuka.'
        );
    }

    private function validated(Request $request, ?Document $document = null): array
    {
        return $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'project_phase_id' => ['nullable', 'exists:project_phases,id'],
            'classification_id' => ['nullable', 'exists:classifications,id'],
            'parent_document_id' => ['nullable', 'exists:documents,id'],
            'nomor_dokumen' => [
                'nullable', 'string', 'max:120',
                Rule::unique('documents', 'nomor_dokumen')
                    ->where(fn ($query) => $query->where('project_id', $request->integer('project_id')))
                    ->ignore($document?->id),
            ],
            'judul' => ['required', 'string', 'max:255'],
            'jenis' => ['required', Rule::in(array_keys(Document::JENIS))],
            'versi' => ['required', 'string', 'max:20'],
            'deskripsi' => ['nullable', 'string'],
            'tanggal_dokumen' => ['required', 'date'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'retention_date' => ['nullable', 'date'],
            'lifecycle_status' => ['required', Rule::in(array_keys(Document::LIFECYCLE_STATUSES))],
            'security_level' => ['required', Rule::in(array_keys(Document::SECURITY_LEVELS))],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);
    }

    private function assertRelations(array $data, Project $project, ?Document $document = null): void
    {
        if (! empty($data['project_phase_id'])) {
            $belongsToProject = ProjectPhase::where('id', $data['project_phase_id'])
                ->where('project_id', $project->id)
                ->exists();

            if (! $belongsToProject) {
                throw ValidationException::withMessages([
                    'project_phase_id' => 'Fase yang dipilih bukan bagian dari proyek ini.',
                ]);
            }
        }

        if (! empty($data['parent_document_id'])) {
            $parent = Document::find($data['parent_document_id']);

            if (! $parent || $parent->project_id !== $project->id || $parent->id === $document?->id) {
                throw ValidationException::withMessages([
                    'parent_document_id' => 'Dokumen induk harus berada pada proyek yang sama.',
                ]);
            }

            if (($data['jenis'] ?? null) === 'addendum' && $parent->jenis !== 'kontrak') {
                throw ValidationException::withMessages([
                    'parent_document_id' => 'Addendum harus terhubung ke kontrak utama, bukan ke addendum lain.',
                ]);
            }

            $ancestor = $parent;

            while ($ancestor) {
                if ($ancestor->id === $document?->id) {
                    throw ValidationException::withMessages([
                        'parent_document_id' => 'Dokumen induk tidak boleh membentuk siklus addendum.',
                    ]);
                }

                $ancestor = $ancestor->parent;
            }
        }
    }

    private function parentOptions(?int $excludeId = null)
    {
        return Document::query()
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->whereIn('jenis', ['kontrak', 'addendum'])
            ->orderBy('tanggal_dokumen')
            ->get(['id', 'project_id', 'judul', 'nomor_dokumen', 'versi', 'jenis']);
    }

    private function generateNomor(Project $project, string $jenis, Carbon $date, ?int $ignoreId = null): string
    {
        $kodeJenis = [
            'kontrak' => 'KTR',
            'addendum' => 'ADD',
            'bast' => 'BAST',
            'izin' => 'IZN',
            'lainnya' => 'UMM',
        ][$jenis] ?? 'DOK';

        $sequence = Document::withTrashed()
            ->where('project_id', $project->id)
            ->where('jenis', $jenis)
            ->whereYear('tanggal_dokumen', $date->year)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->count() + 1;

        $romawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$date->month - 1];

        do {
            $nomor = sprintf('%s/%s/%03d/%s/%d', $project->kode_proyek, $kodeJenis, $sequence, $romawi, $date->year);

            $exists = Document::withTrashed()
                ->where('project_id', $project->id)
                ->where('nomor_dokumen', $nomor)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists();

            $sequence++;
        } while ($exists);

        return $nomor;
    }

    private function storeUploadedFile(UploadedFile $file, Project $project): array
    {
        return [
            'file_path' => $file->store('documents/'.$project->id, 'local'),
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'checksum_sha256' => hash_file('sha256', $file->getRealPath()),
        ];
    }

    private function supersedeParent(Document $document): void
    {
        if (! $document->parent_document_id) {
            return;
        }

        $parent = $document->parent;

        if ($parent && $parent->valid_until === null && $document->valid_from->gt($parent->valid_from)) {
            $parent->update(['valid_until' => $document->valid_from->copy()->subDay()]);
        }
    }

    private function relatedTimeline(Document $document)
    {
        $root = $document->parent ?? $document;

        return Document::where('id', $root->id)
            ->orWhere('parent_document_id', $root->id)
            ->orderBy('valid_from')
            ->get();
    }

    private function authorizeDocument(string $ability, Document $document): void
    {
        try {
            $this->authorize($ability, $document);
        } catch (AuthorizationException $exception) {
            $this->log($document, 'denied');

            throw $exception;
        }
    }

    private function log(Document $document, string $action): void
    {
        DocumentAccessLog::create([
            'document_id' => $document->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 500),
        ]);
    }
}
