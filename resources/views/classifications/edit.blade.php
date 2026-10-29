@extends('layouts.app')

@section('title', 'Ubah Klasifikasi')
@section('page-title', 'Ubah Klasifikasi')
@section('page-subtitle', $classification->kode . ' · ' . $classification->nama)

@section('content')
    <div class="card-soft">
        <div class="card-body">
            <form method="POST" action="{{ route('classifications.update', $classification) }}">
                @csrf
                @method('PUT')
                @include('classifications._form', ['classification' => $classification])
            </form>
        </div>
    </div>
@endsection
