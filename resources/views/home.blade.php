<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TelkomLibrary - Katalog Buku Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
                <i class="bi bi-book"></i> TelkomLibrary
            </a>
            <div class="navbar-nav me-auto ms-3">
                <a class="nav-link active fw-semibold" href="{{ route('home') }}">Katalog Buku</a>
                @auth
                    @if(Auth::user()->role === 'admin')
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    @endif
                @endauth
            </div>
            <div class="d-flex align-items-center gap-2">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm px-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-light btn-sm text-primary fw-semibold px-3">
                        <i class="bi bi-person-plus me-1"></i> Daftar
                    </a>
                @else
                    <span class="text-white small me-2">
                        <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                        <span class="badge bg-light text-primary ms-1">{{ ucfirst(Auth::user()->role) }}</span>
                    </span>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                    </form>
                @endguest
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Notifikasi Error Middleware / Sukses -->
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Judul Halaman -->
        <div class="text-center mb-4">
            <h2 class="fw-bold mb-2">Katalog Koleksi Buku</h2>
            <p class="text-muted">Temukan dan pinjam buku referensi perkuliahan terbaikmu</p>
        </div>

        <!-- Form Pencarian & Filter (Berada DI ATAS Daftar Buku & Sejajar Horizontal) -->
        <div class="card shadow-sm border-0 mb-5 mx-auto" style="max-width: 900px;">
            <div class="card-body p-3">
                <form action="{{ route('home') }}" method="GET" class="row g-2 align-items-center">
                    <!-- Input Search -->
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Cari judul, penulis..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Filter Penerbit -->
                    <div class="col-md-3">
                        <select name="penerbit" class="form-select">
                            <option value="">Semua Penerbit</option>
                            @foreach($penerbitList as $p)
                                <option value="{{ $p }}" {{ request('penerbit') == $p ? 'selected' : '' }}>
                                    {{ $p }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status Stok -->
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                            <option value="habis" {{ request('status') == 'habis' ? 'selected' : '' }}>Stok Habis</option>
                        </select>
                    </div>

                    <!-- Pengurutan (Sort) -->
                    <div class="col-md-2">
                        <select name="sort" class="form-select">
                            <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Urutkan: Terbaru</option>
                            <option value="judul" {{ request('sort') == 'judul' ? 'selected' : '' }}>Judul (A - Z)</option>
                            <option value="tahun" {{ request('sort') == 'tahun' ? 'selected' : '' }}>Tahun Terbit</option>
                            <option value="stok" {{ request('sort') == 'stok' ? 'selected' : '' }}>Stok Terbanyak</option>
                        </select>
                    </div>

                    <!-- Tombol Submit Filter -->
                    <div class="col-md-1 d-grid">
                        <button type="submit" class="btn btn-primary" title="Terapkan Filter">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Grid Katalog Buku (Berada DI BAWAH Form Pencarian) -->
        <div class="row">
            @forelse($buku as $item)
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        @if($item->cover)
                            <img src="{{ asset('storage/' . $item->cover) }}" class="card-img-top" style="height: 240px; object-fit: cover;" alt="{{ $item->judul }}">
                        @else
                            <div class="d-flex flex-column justify-content-center align-items-center bg-secondary bg-opacity-10 text-muted" style="height: 240px;">
                                <i class="bi bi-book fs-1 mb-1 opacity-50"></i>
                                <span class="small">Tidak Ada Sampul</span>
                            </div>
                        @endif

                        <div class="card-body">
                            <h6 class="fw-bold mb-1 text-dark">{{ $item->judul }}</h6>
                            <p class="text-muted small mb-2">{{ $item->penulis }}</p>
                            <span class="badge {{ $item->stok > 0 ? 'bg-success' : 'bg-danger' }}">
                                {{ $item->stok > 0 ? 'Tersedia (' . $item->stok . ')' : 'Stok Habis' }}
                            </span>
                        </div>

                        <div class="card-footer bg-white border-top-0 pb-3">
                            <a href="{{ route('buku.publik.show', $item->id) }}" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-eye me-1"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center text-muted my-5">Buku tidak ditemukan.</p>
                </div>
            @endforelse
        </div>

        <!-- Navigasi Halaman (Pagination) -->
        <div class="d-flex justify-content-center mt-4">
            {{ $buku->links() }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>