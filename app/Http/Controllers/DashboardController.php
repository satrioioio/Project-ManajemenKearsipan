<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $visibleDocuments = Document::query()->visibleTo($user);

        $stats = [
            'projects' => Project::count(),
            'documents' => (clone $visibleDocuments)->count(),
            'contracts' => (clone $visibleDocuments)->whereIn('jenis', ['kontrak', 'addendum'])->count(),
            'locked' => (clone $visibleDocuments)->where('is_locked', true)->count(),
        ];

        $recentDocuments = (clone $visibleDocuments)
            ->with(['project', 'classification'])
            ->latest('tanggal_dokumen')
            ->limit(8)
            ->get();

        $upcomingRetention = (clone $visibleDocuments)
            ->with('project')
            ->whereNotNull('retention_date')
            ->orderBy('retention_date')
            ->limit(5)
            ->get();

        $projects = Project::query()
            ->withCount('phases')
            ->withCount(['documents' => fn ($query) => $query->visibleTo($user)])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'recentDocuments', 'upcomingRetention', 'projects'));
    }
}
