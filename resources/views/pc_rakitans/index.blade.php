@extends('layouts.admin')

@section('title', 'Manajemen Paket Rakitan')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title-bs mb-0">Manajemen Paket PC Rakitan</h2>
        <a href="{{ route('pc-rakitans.create') }}" class="btn">Tambah Paket Baru</a>
    </div>

    @if(session('success'))
        <div class="success" style="margin-bottom: 20px;"><p>{{ session('success') }}</p></div>
    @endif

    @if(session('error'))
        <div class="errors" style="margin-bottom: 20px;"><p>{{ session('error') }}</p></div>
    @endif

    {{-- Total Data --}}
    <div class="mb-2" style="color:#00d9ff;font-weight:bold;">
        Total Paket Rakitan PC: {{ $pakets->count() }}
    </div>

    {{-- Menu search --}}
    <form method="get" class="row g-2 align-items-center mb-3" style="max-width: 600px;">
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama/kategori..." value="{{ request('search') }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-info btn-sm">Search</button>
        </div>
        {{-- Keep sort and dir params --}}
        <input type="hidden" name="sort" value="{{ request('sort', 'name') }}">
        <input type="hidden" name="dir" value="{{ request('dir', 'asc') }}">
    </form>

    @php
        $sort = request('sort', 'name');
        $dir = request('dir', 'asc');
        // Filter by search
        $filtered = $pakets;
        if(request('search')) {
            $filtered = $filtered->filter(function($paket) {
                $q = strtolower(request('search'));
                return str_contains(strtolower($paket->name), $q)
                    || str_contains(strtolower($paket->category), $q);
            });
        }
        // Sort
        $sorted = $filtered->sortBy(function($paket) use ($sort) {
            if ($sort === 'specs') {
                return is_array($paket->specs) ? implode(' ', array_keys($paket->specs)) : '';
            }
            return $paket->{$sort};
        }, SORT_REGULAR, $dir === 'desc');
        // Pagination (manual)
        $perPage = 10;
        $page = max(1, intval(request('page', 1)));
        $total = $sorted->count();
        $paged = $sorted->slice(($page-1)*$perPage, $perPage);
        function sort_link_paket($label, $col) {
            $currentSort = request('sort', 'name');
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
    @endphp

    <div class="table-responsive">
        <table class="table table-dark table-striped align-middle table-bordered">
            <thead>
                <tr>
                    <th>{!! sort_link_paket('Gambar', 'image') !!}</th>
                    <th>{!! sort_link_paket('Nama Paket', 'name') !!}</th>
                    <th>{!! sort_link_paket('Kategori', 'category') !!}</th>
                    <th>{!! sort_link_paket('Harga', 'price') !!}</th>
                    <th>{!! sort_link_paket('Spesifikasi Utama', 'specs') !!}</th>
                    <th style="width: 15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paged as $paket)
                    <tr>
                        <td>
                            <img src="{{ $paket->image }}" alt="{{ $paket->name }}" style="max-width: 70px; margin-right: 15px; border-radius: 4px;">
                        </td>
                        <td>{{ $paket->name }}</td>
                        <td>{{ $paket->category }}</td>
                        <td>Rp {{ number_format($paket->price, 0, ',', '.') }}</td>
                        <td>
                            @if(is_array($paket->specs) && !empty($paket->specs))
                                <ul class="list-unstyled mb-0" style="font-size: 0.85rem;">
                                    @foreach(array_slice($paket->specs, 0, 3) as $key => $value)
                                        <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                                    @endforeach
                                    @if(count($paket->specs) > 3)
                                        <li>...</li>
                                    @endif
                                </ul>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pc-rakitans.edit', $paket->id) }}" class="btn-edit">Edit</a>
                            <form action="{{ route('pc-rakitans.destroy', $paket->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data paket rakitan.</td>
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