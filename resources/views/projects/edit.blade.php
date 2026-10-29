@extends('layouts.app')

@section('title', 'Ubah Proyek')
@section('page-title', 'Ubah Proyek')
@section('page-subtitle', $project->nama_proyek)

@section('content')
    <div class="card-soft">
        <div class="card-body">
            <form method="POST" action="{{ route('projects.update', $project) }}">
                @csrf
                @method('PUT')
                @include('projects._form', ['project' => $project])
            </form>
        </div>
    </div>
@endsection
