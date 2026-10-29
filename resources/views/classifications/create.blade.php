@extends('layouts.app')

@section('title', 'Klasifikasi Baru')
@section('page-title', 'Klasifikasi Arsip Baru')
@section('page-subtitle', 'Tambahkan kode klasifikasi dan jadwal retensinya')

@section('content')
    <div class="card-soft">
        <div class="card-body">
            <form method="POST" action="{{ route('classifications.store') }}">
                @csrf
                @include('classifications._form', ['classification' => $classification])
            </form>
        </div>
    </div>
@endsection
