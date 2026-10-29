<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::query()
            ->search($request->string('q')->toString())
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->withCount([
                'phases',
                'documents' => fn ($query) => $query->visibleTo($request->user()),
            ])
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'statuses' => Project::STATUSES,
        ]);
    }

    public function create()
    {
        return view('projects.create', [
            'project' => new Project(['status' => 'berjalan']),
            'statuses' => Project::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        $project = Project::create($data);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Proyek berhasil ditambahkan.');
    }

    public function show(Request $request, Project $project)
    {
        $user = $request->user();

        $documents = $project->documents()
            ->visibleTo($user)
            ->with(['phase', 'classification', 'parent'])
            ->orderBy('valid_from')
            ->get();

        $phases = $project->phases()->get();

        $phases->each(function ($phase) use ($documents) {
            $phase->visible_documents_count = $documents->where('project_phase_id', $phase->id)->count();
        });

        return view('projects.show', compact('project', 'phases', 'documents'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', [
            'project' => $project,
            'statuses' => Project::STATUSES,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $project->update($this->validated($request, $project));

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Data proyek berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        if ($project->documents()->exists()) {
            return back()->with('error', 'Proyek tidak dapat dihapus karena masih memiliki dokumen arsip.');
        }

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Proyek berhasil dihapus.');
    }

    private function validated(Request $request, ?Project $project = null): array
    {
        return $request->validate([
            'kode_proyek' => [
                'required', 'string', 'max:50',
                Rule::unique('projects', 'kode_proyek')->ignore($project?->id),
            ],
            'nama_proyek' => ['required', 'string', 'max:255'],
            'pemilik_pekerjaan' => ['nullable', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'nilai_kontrak' => ['nullable', 'numeric', 'min:0'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', Rule::in(array_keys(Project::STATUSES))],
            'deskripsi' => ['nullable', 'string'],
        ]);
    }
}
