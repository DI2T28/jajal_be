@extends('layouts.admin')
@section('title', 'Manajemen Berita Teknologi')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title-bs mb-0">Manajemen Berita Teknologi</h2>
        <a href="{{ route('tech-news.create') }}" class="btn">Tambah Berita Baru</a>
    </div>
    @if(session('success'))
        <div class="success" style="margin-bottom: 20px;"><p>{{ session('success') }}</p></div>
    @endif
    @if(session('error'))
        <div class="errors" style="margin-bottom: 20px;"><p>{{ session('error') }}</p></div>
    @endif

    {{-- Total Data --}}
    <div class="mb-2" style="color:#00d9ff;font-weight:bold;">
        Total Berita: {{ $newsItems->count() }}
    </div>

    {{-- Menu search --}}
    <form method="get" class="row g-2 align-items-center mb-3" style="max-width: 600px;">
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari judul/sumber..." value="{{ request('search') }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-info btn-sm">Search</button>
        </div>
        {{-- Keep sort and dir params --}}
        <input type="hidden" name="sort" value="{{ request('sort', 'date') }}">
        <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">
    </form>

    @php
        $sort = request('sort', 'date');
        $dir = request('dir', 'desc');
        // Filter by search
        $filtered = $newsItems;
        if(request('search')) {
            $filtered = $filtered->filter(function($news) {
                $q = strtolower(request('search'));
                return str_contains(strtolower($news->title), $q)
                    || str_contains(strtolower($news->source), $q);
            });
        }
        // Sort
        $sorted = $filtered->sortBy(function($news) use ($sort) {
            if ($sort === 'date') {
                return $news->date;
            }
            return $news->{$sort};
        }, SORT_REGULAR, $dir === 'desc');
        // Pagination (manual)
        $perPage = 10;
        $page = max(1, intval(request('page', 1)));
        $total = $sorted->count();
        $paged = $sorted->slice(($page-1)*$perPage, $perPage);
        function sort_link_news($label, $col) {
            $currentSort = request('sort', 'date');
            $currentDir = request('dir', 'desc');
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
                    <th>{!! sort_link_news('Gambar', 'imageUrl') !!}</th>
                    <th>{!! sort_link_news('Tanggal', 'date') !!}</th>
                    <th>{!! sort_link_news('Judul', 'title') !!}</th>
                    <th>{!! sort_link_news('Sumber', 'source') !!}</th>
                    <th style="width: 15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paged as $news)
                    <tr>
                        <td class="text-center">
                            @if($news->imageUrl)
                                <img src="{{ $news->imageUrl }}" alt="Gambar" style="max-width: 80px; max-height: 60px; border-radius: 6px;">
                            @else
                                <span style="color:#888;">-</span>
                            @endif
                        </td>
                        <td>{{ $news->date->format('d M Y') }}</td>
                        <td>{{ $news->title }}</td>
                        <td>{{ $news->source }}</td>
                        <td>
                            <a href="{{ route('tech-news.edit', $news->id) }}" class="btn-edit">Edit</a>
                            <form action="{{ route('tech-news.destroy', $news->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">Belum ada berita.</td></tr>
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