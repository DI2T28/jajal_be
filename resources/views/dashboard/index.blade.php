@extends('layouts.admin')

@section('title', 'Dashboard Utama')

@section('content')
    {{-- Judul Halaman --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title-bs mb-0">Daftar Pengguna</h2>
        <a href="{{ route('users.create') }}" class="btn">Tambah User Baru</a>
    </div>

    {{-- Tampilkan pesan sukses atau error dari session --}}
    @if(session('success'))
        <div class="success" style="margin-bottom: 20px;"><p>{{ session('success') }}</p></div>
    @endif
    @if(session('error'))
        <div class="errors" style="margin-bottom: 20px;"><p>{{ session('error') }}</p></div>
    @endif

    {{-- Total User --}}
    <div class="mb-2" style="color:#00d9ff;font-weight:bold;">
        Total User: {{ $users->count() }}
    </div>

    {{-- Menu tombol search --}}
    <form method="get" class="row g-2 align-items-center mb-3" style="max-width: 600px;">
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari user..." value="{{ request('search') }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-info btn-sm">Search</button>
        </div>
        {{-- Keep sort and dir params --}}
        <input type="hidden" name="sort" value="{{ request('sort', 'id') }}">
        <input type="hidden" name="dir" value="{{ request('dir', 'asc') }}">
    </form>

    @php
        $sort = request('sort', 'id');
        $dir = request('dir', 'asc');
        function sort_link_user($label, $col) {
            $currentSort = request('sort', 'id');
            $currentDir = request('dir', 'asc');
            $newDir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
            $icon = '';
            if ($currentSort === $col) {
                $icon = $currentDir === 'asc' ? '↑' : '↓';
            }
            $params = array_merge(request()->except(['sort', 'dir', 'page']), ['sort' => $col, 'dir' => $newDir]);
            $url = url()->current() . '?' . http_build_query($params);
            return '<a href="' . $url . '" style="color:#00d9ff;">' . $label . ' ' . $icon . '</a>';
        }

        // Paging logic
        $perPage = 10;
        $page = max(1, intval(request('page', 1)));
        // Filter by search
        $filtered = $users;
        if(request('search')) {
            $filtered = $filtered->filter(function($user) {
                $q = strtolower(request('search'));
                return str_contains(strtolower($user->username), $q)
                    || str_contains(strtolower($user->email), $q)
                    || str_contains(strtolower($user->role), $q);
            });
        }
        // Sort
        $sorted = $filtered->sortBy(function($user) use ($sort) {
            if ($sort === 'created_at') {
                return $user->created_at;
            }
            return $user->{$sort};
        }, SORT_REGULAR, $dir === 'desc');
        // Pagination (manual, since $users is a collection)
        $total = $sorted->count();
        $paged = $sorted->slice(($page-1)*$perPage, $perPage);
    @endphp

    {{-- Tabel Daftar User --}}
    <div class="table-responsive">
        <table class="table table-dark table-striped align-middle table-bordered">
            <thead>
                <tr>
                    <th>{!! sort_link_user('ID', 'id') !!}</th>
                    <th>{!! sort_link_user('Username', 'username') !!}</th>
                    <th>{!! sort_link_user('Email', 'email') !!}</th>
                    <th>{!! sort_link_user('Role', 'role') !!}</th>
                    <th>{!! sort_link_user('Tanggal Daftar', 'created_at') !!}</th>
                    <th style="width: 15%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paged as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->role) }}</td>
                        <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
                        <td>
                            <a href="{{ route('users.edit', $user->id) }}" class="btn-edit">Edit</a>
                            {{-- Tombol Hapus hanya muncul jika user yang akan dihapus BUKAN user yang sedang login --}}
                            @if(auth()->id() !== $user->id)
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada user terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination links --}}
    @php
        $lastPage = ceil($total / $perPage);
        $query = request()->except('page');
    @endphp
    @if($lastPage > 1)
        <nav>
            <ul class="pagination pagination-sm">
                @for($i = 1; $i <= $lastPage; $i++)
                    <li class="page-item @if($i == $page) active @endif">
                        <a class="page-link" href="{{ url()->current() . '?' . http_build_query(array_merge($query, ['page' => $i])) }}">{{ $i }}</a>
                    </li>
                @endfor
            </ul>
        </nav>
    @endif

@endsection