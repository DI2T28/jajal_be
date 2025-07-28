@extends('layouts.admin')

@section('title', 'Manajemen Laptop')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title-bs mb-0">Manajemen Data Laptop</h2>
        <a href="{{ route('laptops.create') }}" class="btn">Tambah Laptop Baru</a>
    </div>

    @if(session('success'))
        <div class="success" style="margin-bottom: 20px;"><p>{{ session('success') }}</p></div>
    @endif

    @if(session('error'))
        <div class="errors" style="margin-bottom: 20px;"><p>{{ session('error') }}</p></div>
    @endif

    {{-- Total Data --}}
    <div class="mb-2" style="color:#00d9ff;font-weight:bold;">
        Total Data Laptop: {{ $laptops->count() }}
    </div>

    {{-- Menu search --}}
    <form method="get" class="row g-2 align-items-center mb-3" style="max-width: 600px;">
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama/brand/kategori..." value="{{ request('search') }}">
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
        $filtered = $laptops;
        if(request('search')) {
            $filtered = $filtered->filter(function($laptop) {
                $q = strtolower(request('search'));
                return str_contains(strtolower($laptop->name), $q)
                    || str_contains(strtolower($laptop->brand), $q)
                    || str_contains(strtolower($laptop->category), $q);
            });
        }
        // Sort
        $sorted = $filtered->sortBy(function($laptop) use ($sort) {
            return $laptop->{$sort};
        }, SORT_REGULAR, $dir === 'desc');
        // Pagination (manual)
        $perPage = 10;
        $page = max(1, intval(request('page', 1)));
        $total = $sorted->count();
        $paged = $sorted->slice(($page-1)*$perPage, $perPage);
        function sort_link_laptop($label, $col) {
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
                    <th>{!! sort_link_laptop('Gambar', 'image') !!}</th>
                    <th>{!! sort_link_laptop('Nama', 'name') !!}</th>
                    <th>{!! sort_link_laptop('Brand', 'brand') !!}</th>
                    <th>{!! sort_link_laptop('Kategori', 'category') !!}</th>
                    <th>{!! sort_link_laptop('Harga', 'price') !!}</th>
                    <th>{!! sort_link_laptop('Stok', 'stock') !!}</th>
                    <th style="width: 15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paged as $laptop)
                    <tr>
                        <td>
                            <img src="{{ $laptop->image }}" alt="{{ $laptop->name }}" style="max-width: 60px; border-radius: 4px;">
                        </td>
                        <td>{{ $laptop->name }}</td>
                        <td>{{ $laptop->brand }}</td>
                        <td>{{ $laptop->category }}</td>
                        <td>Rp {{ number_format($laptop->price, 0, ',', '.') }}</td>
                        <td>{{ $laptop->stock }}</td>
                        <td>
                            <a href="{{ route('laptops.edit', $laptop->id) }}" class="btn-edit">Edit</a>
                            <form action="{{ route('laptops.destroy', $laptop->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data laptop.</td>
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