@extends('layouts.app')

@section('title', 'Ubah Pengguna')
@section('page-title', 'Ubah Pengguna')
@section('page-subtitle', $user->name . ' · ' . $user->email)

@section('content')
    <div class="card-soft">
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                @include('users._form', ['user' => $user])
            </form>
        </div>
    </div>
@endsection
