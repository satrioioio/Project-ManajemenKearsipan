@extends('layouts.app')

@section('title', 'Ubah Dokumen')
@section('page-title', 'Ubah Dokumen')
@section('page-subtitle', $document->nomor_dokumen . ' · ' . $document->judul)

@section('content')
    <div class="card-soft">
        <div class="card-body">
            @if ($document->is_locked)
                <div class="alert alert-warning">
                    <i class="bi bi-lock-fill me-1"></i>
                    Dokumen ini terkunci sebagai arsip resmi. Buka kunci terlebih dahulu bila memang perlu diubah.
                </div>
            @endif

            <form method="POST" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('documents._form', ['document' => $document])
            </form>
        </div>
    </div>
@endsection
