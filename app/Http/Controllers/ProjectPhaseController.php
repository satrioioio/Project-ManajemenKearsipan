<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPhase;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectPhaseController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $project->phases()->create($this->validated($request));

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Fase proyek berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project, ProjectPhase $phase)
    {
        abort_unless($phase->project_id === $project->id, 404);

        $phase->update($this->validated($request));

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Fase proyek berhasil diperbarui.');
    }

    public function destroy(Project $project, ProjectPhase $phase)
    {
        abort_unless($phase->project_id === $project->id, 404);

        if ($phase->documents()->exists()) {
            return back()->with('error', 'Fase tidak dapat dihapus karena masih terkait dokumen arsip.');
        }

        $phase->delete();

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Fase proyek berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'urutan' => ['required', 'integer', 'min:1', 'max:99'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', Rule::in(array_keys(ProjectPhase::STATUSES))],
            'deskripsi' => ['nullable', 'string'],
        ]);
    }
}
