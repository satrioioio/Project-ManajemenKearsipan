<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassificationController extends Controller
{
    public function index(Request $request)
    {
        $classifications = Classification::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q')->toString();
                $query->where('kode', 'like', "%{$term}%")->orWhere('nama', 'like', "%{$term}%");
            })
            ->with('parent')
            ->withCount('documents')
            ->orderBy('kode')
            ->paginate(15)
            ->withQueryString();

        return view('classifications.index', compact('classifications'));
    }

    public function create()
    {
        return view('classifications.create', [
            'classification' => new Classification(['retensi_tahun' => 10]),
            'parents' => Classification::orderBy('kode')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Classification::create($this->validated($request));

        return redirect()
            ->route('classifications.index')
            ->with('success', 'Klasifikasi arsip berhasil ditambahkan.');
    }

    public function edit(Classification $classification)
    {
        return view('classifications.edit', [
            'classification' => $classification,
            'parents' => Classification::where('id', '!=', $classification->id)->orderBy('kode')->get(),
        ]);
    }

    public function update(Request $request, Classification $classification)
    {
        $classification->update($this->validated($request, $classification));

        return redirect()
            ->route('classifications.index')
            ->with('success', 'Klasifikasi arsip berhasil diperbarui.');
    }

    public function destroy(Classification $classification)
    {
        if ($classification->documents()->exists() || $classification->children()->exists()) {
            return back()->with('error', 'Klasifikasi tidak dapat dihapus karena masih digunakan dokumen atau sub-klasifikasi.');
        }

        $classification->delete();

        return redirect()
            ->route('classifications.index')
            ->with('success', 'Klasifikasi arsip berhasil dihapus.');
    }

    private function validated(Request $request, ?Classification $classification = null): array
    {
        return $request->validate([
            'kode' => [
                'required', 'string', 'max:30',
                Rule::unique('classifications', 'kode')->ignore($classification?->id),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable', 'exists:classifications,id',
                Rule::notIn([$classification?->id]),
            ],
            'retensi_tahun' => ['required', 'integer', 'min:1', 'max:100'],
            'keterangan' => ['nullable', 'string'],
        ]);
    }
}
