@extends('layouts.app')

@section('title', 'Pengguna Baru')
@section('page-title', 'Pengguna Baru')
@section('page-subtitle', 'Tambahkan akun dan tentukan perannya')

@section('content')
    <div class="card-soft">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                @include('users._form', ['user' => $user])
            </form>
        </div>
    </div>
@endsection
