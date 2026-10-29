@extends('layouts.app')

@section('title', 'Unggah Dokumen')
@section('page-title', 'Unggah Dokumen Arsip')
@section('page-subtitle', 'Penciptaan arsip baru (creation / capture)')

@section('content')
    <div class="card-soft">
        <div class="card-body">
            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                @csrf
                @include('documents._form', ['document' => $document])
            </form>
        </div>
    </div>
@endsection
