@extends('layouts.admin')

@section('title', 'Tambah PC Part Baru')

@section('content')
    <h2 class="section-title-bs">Tambah Data PC Part</h2>

    {{-- Bagian untuk menampilkan error validasi --}}
    @if ($errors->any())
        <div class="errors" style="margin-bottom: 20px;">
            <strong>Whoops! Ada beberapa masalah dengan input Anda.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Menggunakan class admin-card-bs untuk styling konsisten --}}
    <form class="admin-card-bs p-4" action="{{ route('pc-parts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Field: Name (varchar) --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nama PC Part</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        {{-- Field: Price (unsigned bigint) --}}
        <div class="mb-3">
            <label for="price" class="form-label">Harga</label>
            <input type="number" name="price" id="price" class="form-control" value="{{ old('price') }}" required min="0">
        </div>

        {{-- Field: Brand (varchar) --}}
        <div class="mb-3">
            <label for="brand" class="form-label">Brand</label>
            <input type="text" name="brand" id="brand" class="form-control" value="{{ old('brand') }}" required>
        </div>
        
        {{-- Field: Category (varchar) dengan dropdown --}}
        <div class="mb-3">
            <label for="category" class="form-label">Kategori</label>
            <select name="category" id="category" class="form-control" required>
                <option value="" disabled selected>Pilih Kategori</option>
                <option value="PROCESSOR INTEL" @if(old('category') == 'PROCESSOR INTEL') selected @endif>PROCESSOR INTEL</option>
                <option value="PROCESSOR AMD" @if(old('category') == 'PROCESSOR AMD') selected @endif>PROCESSOR AMD</option>
                <option value="MAINBOARD" @if(old('category') == 'MAINBOARD') selected @endif>MAINBOARD</option>
                <option value="MEMORY" @if(old('category') == 'MEMORY') selected @endif>MEMORY</option>
                <option value="VGA" @if(old('category') == 'VGA') selected @endif>VGA</option>
                <option value="HDD" @if(old('category') == 'HDD') selected @endif>HDD</option>
                <option value="SSD" @if(old('category') == 'SSD') selected @endif>SSD</option>
                <option value="PSU" @if(old('category') == 'PSU') selected @endif>PSU</option>
                <option value="CASE" @if(old('category') == 'CASE') selected @endif>CASE</option>
                <option value="LED MONITOR" @if(old('category') == 'LED MONITOR') selected @endif>LED MONITOR</option>
                <option value="MOUSE" @if(old('category') == 'MOUSE') selected @endif>MOUSE</option>
                <option value="KEYBOARD" @if(old('category') == 'KEYBOARD') selected @endif>KEYBOARD</option>
                <option value="MOUSEPAD" @if(old('category') == 'MOUSEPAD') selected @endif>MOUSEPAD</option>
                <option value="WEBCAM" @if(old('category') == 'WEBCAM') selected @endif>WEBCAM</option>
                <option value="CABLE" @if(old('category') == 'CABLE') selected @endif>CABLE</option>
                <option value="HEADSET" @if(old('category') == 'HEADSET') selected @endif>HEADSET</option>
                <option value="SPEAKER" @if(old('category') == 'SPEAKER') selected @endif>SPEAKER</option>
                <option value="USB FLASHDISK" @if(old('category') == 'USB FLASHDISK') selected @endif>USB FLASHDISK</option>
                <option value="PRINTER" @if(old('category') == 'PRINTER') selected @endif>PRINTER</option>
            </select>
        </div>

        {{-- ========================================================== --}}
        {{-- BAGIAN GAMBAR YANG DIPERBARUI --}}
        {{-- ========================================================== --}}
        <div class="mb-3">
            <label class="form-label">Sumber Gambar</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="image_source_type" id="source_url" value="url" checked>
                <label class="form-check-label" for="source_url">Gunakan Link (URL)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="image_source_type" id="source_upload" value="upload">
                <label class="form-check-label" for="source_upload">Upload dari Komputer</label>
            </div>
        </div>
        <div id="image-url-group" class="mb-3">
            <label for="image" class="form-label">URL Gambar</label>
            <input type="text" name="image" id="image" class="form-control" value="{{ old('image') }}">
        </div>
        <div id="image-upload-group" class="mb-3" style="display: none;">
            <label for="image_upload" class="form-label">Upload File Gambar</label>
            <input type="file" name="image_upload" id="image_upload" class="form-control" accept="image/*">
        </div>
        {{-- ========================================================== --}}

        {{-- Field: Stock (int) --}}
        <div class="mb-3">
            <label for="stock" class="form-label">Stok</label>
            <input type="number" name="stock" id="stock" class="form-control" value="{{ old('stock', 0) }}" required min="0">
        </div>

        {{-- Tombol Submit --}}
        <button type="submit" class="login-btn-bs w-100">Simpan Data</button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlRadio = document.getElementById('source_url');
            const uploadRadio = document.getElementById('source_upload');
            const urlGroup = document.getElementById('image-url-group');
            const uploadGroup = document.getElementById('image-upload-group');
            const urlInput = document.getElementById('image');
            const uploadInput = document.getElementById('image_upload');

            function toggleInputs() {
                if (urlRadio.checked) {
                    urlGroup.style.display = 'block';
                    uploadGroup.style.display = 'none';
                    uploadInput.value = '';
                } else {
                    urlGroup.style.display = 'none';
                    uploadGroup.style.display = 'block';
                    urlInput.value = '';
                }
            }

            urlRadio.addEventListener('change', toggleInputs);
            uploadRadio.addEventListener('change', toggleInputs);
            toggleInputs();
        });
    </script>
@endsection