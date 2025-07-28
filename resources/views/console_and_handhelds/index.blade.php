@extends('layouts.admin')
@section('title', 'Daftar Konsol & Handheld')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title-bs mb-0">Daftar Konsol & Handheld</h2>
        <a href="{{ route('console-and-handhelds.create') }}" class="btn">Tambah Data Baru</a>
    </div>
    @if(session('success'))
        <div class="success" style="margin-bottom: 20px;"><p>{{ session('success') }}</p></div>
    @endif
    @if(session('error'))
        <div class="errors" style="margin-bottom: 20px;"><p>{{ session('error') }}</p></div>
    @endif

    {{-- Total Data --}}
    <div class="mb-2" style="color:#00d9ff;font-weight:bold;">
        Total Data Console and Handheld: {{ $consoles->count() }}
    </div>

    {{-- Menu search --}}
    <form method="get" class="row g-2 align-items-center mb-3" style="max-width: 600px;">
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari konsol/brand..." value="{{ request('search') }}">
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
        $filtered = $consoles;
        if(request('search')) {
            $filtered = $filtered->filter(function($console) {
                $q = strtolower(request('search'));
                return str_contains(strtolower($console->name), $q)
                    || str_contains(strtolower($console->brand), $q)
                    || str_contains(strtolower($console->category), $q);
            });
        }
        // Sort
        $sorted = $filtered->sortBy(function($console) use ($sort) {
            if ($sort === 'specs') {
                return is_array($console->specs) ? implode(' ', $console->specs) : '';
            }
            return $console->{$sort};
        }, SORT_REGULAR, $dir === 'desc');
        // Pagination (manual)
        $perPage = 10;
        $page = max(1, intval(request('page', 1)));
        $total = $sorted->count();
        $paged = $sorted->slice(($page-1)*$perPage, $perPage);
        function sort_link_console($label, $col) {
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
                    <th>{!! sort_link_console('Gambar', 'image') !!}</th>
                    <th>{!! sort_link_console('Nama', 'name') !!}</th>
                    <th>{!! sort_link_console('Brand', 'brand') !!}</th>
                    <th>{!! sort_link_console('Kategori', 'category') !!}</th>
                    <th>{!! sort_link_console('Harga', 'price') !!}</th>
                    <th>{!! sort_link_console('Spesifikasi', 'specs') !!}</th>
                    <th>{!! sort_link_console('Stok', 'stock') !!}</th>
                    <th style="width: 15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paged as $console)
                    <tr>
                        <td>
                            <img src="{{ $console->image }}" alt="{{ $console->name }}" style="max-width: 60px; border-radius: 4px;">
                        </td>
                        <td>{{ $console->name }}</td>
                        <td>{{ $console->brand }}</td>
                        <td>{{ $console->category }}</td>
                        <td>Rp {{ number_format($console->price, 0, ',', '.') }}</td>
                        <td>
                            <ul class="mb-0" style="font-size: 0.95em;">
                                @foreach($console->specs as $spec)
                                    <li>{{ $spec }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>{{ $console->stock }}</td>
                        <td>
                            <a href="{{ route('console-and-handhelds.edit', $console->id) }}" class="btn-edit">Edit</a>
                            <form action="{{ route('console-and-handhelds.destroy', $console->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">Belum ada data.</td></tr>
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