@extends('layouts.admin')

@section('title', 'Pesan Customer Service')

@section('content')
    <h2 class="section-title-bs">Pesan Masuk dari Customer</h2>

    {{-- Total Pesan --}}
    <div class="mb-2" style="color:#00d9ff;font-weight:bold;">
        Total Pesan: {{ $messages->count() }}
    </div>

    {{-- Menu search --}}
    <form method="get" class="row g-2 align-items-center mb-3" style="max-width: 600px;">
        <div class="col-auto">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama/email/pesan..." value="{{ request('search') }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-info btn-sm">Search</button>
        </div>
        {{-- Keep sort and dir params --}}
        <input type="hidden" name="sort" value="{{ request('sort', 'created_at') }}">
        <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">
    </form>

    @php
        $sort = request('sort', 'created_at');
        $dir = request('dir', 'desc');
        // Filter by search
        $filtered = $messages;
        if(request('search')) {
            $filtered = $filtered->filter(function($msg) {
                $q = strtolower(request('search'));
                return str_contains(strtolower($msg->nama), $q)
                    || str_contains(strtolower($msg->email), $q)
                    || str_contains(strtolower($msg->pesan), $q);
            });
        }
        // Sort
        $sorted = $filtered->sortBy(function($msg, $idx) use ($sort) {
            if ($sort === 'no') return $idx;
            if ($sort === 'created_at') return $msg->created_at;
            return $msg->{$sort};
        }, SORT_REGULAR, $dir === 'desc');
        // Pagination (manual)
        $perPage = 10;
        $page = max(1, intval(request('page', 1)));
        $total = $sorted->count();
        $paged = $sorted->slice(($page-1)*$perPage, $perPage);
        function sort_link_cs($label, $col) {
            $currentSort = request('sort', 'created_at');
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

    @if($paged->isEmpty())
        <div class="alert alert-info" style="background-color: #1f2937; border-color: #00d9ff; color: #e8eff5;">
            Belum ada pesan yang masuk.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-dark table-striped align-middle table-bordered">
                <thead>
                    <tr>
                        <th style="width: 4%;">{!! sort_link_cs('No', 'no') !!}</th>
                        <th style="width: 12%;">{!! sort_link_cs('Tanggal Kirim', 'created_at') !!}</th>
                        <th style="width: 12%;">{!! sort_link_cs('Nama', 'nama') !!}</th>
                        <th style="width: 16%;">{!! sort_link_cs('Email', 'email') !!}</th>
                        <th style="width: 36%;">{!! sort_link_cs('Pesan', 'pesan') !!}</th>
                        <th style="width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paged->values() as $index => $message)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $message->created_at->format('d M Y, H:i') }}</td>
                            <td>{{ $message->nama }}</td>
                            <td>{{ $message->email }}</td>
                            <td style="white-space: pre-wrap;">{{ $message->pesan }}</td>
                            <td>
                                {{-- ========================================================== --}}
                                <!-- FORM DAN TOMBOL HAPUS -->
                                {{-- ========================================================== --}}
                                <form action="{{ route('customer-service.destroy', $message->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete">Hapus</button>
                                </form>
                                {{-- ========================================================== --}}
                            </td>
                        </tr>
                    @endforeach
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
    @endif
@endsection