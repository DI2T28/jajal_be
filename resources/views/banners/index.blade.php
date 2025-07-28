@extends('layouts.admin')

@section('title', 'Manajemen Banner')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title-bs mb-0">Manajemen Banner Halaman Utama</h2>
        <a href="{{ route('banners.create') }}" class="btn">Tambah Banner Baru</a>
    </div>

    {{-- Total Banner --}}
    <div class="mb-2" style="color:#00d9ff;font-weight:bold;">
        Total Banner: {{ $banners->count() }}
    </div>

    {{-- Menu search --}}
    <form method="get" class="row g-2 align-items-center mb-3" style="max-width: 600px;">
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari banner..." value="{{ request('search') }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-info btn-sm">Search</button>
        </div>
        {{-- Keep sort and dir params --}}
        <input type="hidden" name="sort" value="{{ request('sort', 'order_column') }}">
        <input type="hidden" name="dir" value="{{ request('dir', 'asc') }}">
    </form>

    @php
        $sort = request('sort', 'order_column');
        $dir = request('dir', 'asc');
        // Filter by search
        $filtered = $banners;
        if(request('search')) {
            $filtered = $filtered->filter(function($banner) {
                $q = strtolower(request('search'));
                return str_contains(strtolower($banner->brand), $q)
                    || str_contains(strtolower($banner->name), $q)
                    || str_contains(strtolower($banner->is_active ? 'aktif' : 'tidak aktif'), $q);
            });
        }
        // Sort
        $sorted = $filtered->sortBy(function($banner) use ($sort) {
            // Support for nested sort (brand & name)
            if ($sort === 'brand') {
                return $banner->brand . ' ' . $banner->name;
            }
            return $banner->{$sort};
        }, SORT_REGULAR, $dir === 'desc');
        // Pagination (manual)
        $perPage = 10;
        $page = max(1, intval(request('page', 1)));
        $total = $sorted->count();
        $paged = $sorted->slice(($page-1)*$perPage, $perPage);

        // Define sort_link function here
        function sort_link($label, $col) {
            $currentSort = request('sort', 'order_column');
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

    @if(session('success'))
        <div class="success" style="margin-bottom: 20px;"><p>{{ session('success') }}</p></div>
    @endif

    @if(session('error'))
        <div class="errors" style="margin-bottom: 20px;"><p>{{ session('error') }}</p></div>
    @endif

    <div class="table-responsive">
        <table class="table table-dark table-striped align-middle table-bordered">
            <thead>
                <tr>
                    <th>{!! sort_link('Urutan', 'order_column') !!}</th>
                    <th>{!! sort_link('Gambar', 'imageSrc') !!}</th>
                    <th>{!! sort_link('Brand & Nama', 'brand') !!}</th>
                    <th>{!! sort_link('Status', 'is_active') !!}</th>
                    <th style="width: 15%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paged as $banner)
                    <tr>
                        <td>{{ $banner->order_column }}</td>
                        <td>
                            <img src="{{ $banner->imageSrc }}" alt="{{ $banner->name }}" style="max-width: 100px; border-radius: 4px;">
                        </td>
                        <td>
                            <strong>{{ $banner->brand }}</strong><br>
                            {{ $banner->name }}
                        </td>
                        <td>
                            @if($banner->is_active)
                                <span style="color: #28f57a;">Aktif</span>
                            @else
                                <span style="color: #ff4d4d;">Tidak Aktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('banners.edit', $banner->id) }}" class="btn-edit">Edit</a>
                            <form action="{{ route('banners.destroy', $banner->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus banner ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada banner yang ditambahkan.</td>
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