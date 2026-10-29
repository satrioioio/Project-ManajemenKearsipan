@extends('layouts.app')

@section('title', 'Tambah Proyek')
@section('page-title', 'Tambah Proyek')
@section('page-subtitle', 'Daftarkan proyek konstruksi baru')

@section('content')
    <div class="card-soft">
        <div class="card-body">
            <form method="POST" action="{{ route('projects.store') }}">
                @csrf
                @include('projects._form', ['project' => $project])
            </form>
        </div>
    </div>
@endsection
