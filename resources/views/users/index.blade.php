@extends('layouts.app')

@section('title', 'Pengguna')
@section('page-title', 'Manajemen Pengguna')
@section('page-subtitle', 'Akun dan hak akses berdasarkan peran')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <form class="d-flex gap-2" method="GET" action="{{ route('users.index') }}">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" style="min-width: 240px"
                placeholder="Cari nama atau email...">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        </form>

        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Pengguna Baru
        </a>
    </div>

    <div class="card-soft">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Jabatan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="user-avatar" style="width:32px;height:32px;font-size:.8rem">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                    <span class="fw-semibold">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="small text-secondary">{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleColor = match ($user->role) {
                                        'admin' => 'danger',
                                        'legal' => 'primary',
                                        'pm' => 'info',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $roleColor }} bg-opacity-10 text-{{ $roleColor }}">{{ $user->roleLabel() }}</span>
                            </td>
                            <td class="small">{{ $user->jabatan ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $user->is_active ? 'success' : 'secondary' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if ($user->id !== auth()->id())
                                    <form class="d-inline" method="POST" action="{{ route('users.destroy', $user) }}"
                                        onsubmit="return confirm('Hapus pengguna ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="bi bi-people"></i>
                                Belum ada pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
