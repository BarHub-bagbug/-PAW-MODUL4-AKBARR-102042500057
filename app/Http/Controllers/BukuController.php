<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BukuController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->input('search');
        $penerbit = $request->input('penerbit');
        $status   = $request->input('status');
        $sort     = $request->input('sort');

        $buku = Buku::when($search, function ($query, $search) {
            return $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('penulis', 'like', "%{$search}%")
                  ->orWhere('penerbit', 'like', "%{$search}%");
            });
        })
        ->when($penerbit, function ($query, $penerbit) {
            return $query->where('penerbit', $penerbit);
        })
        ->when($status, function ($query, $status) {
            if ($status === 'tersedia') {
                return $query->where('stok', '>', 0);
            } elseif ($status === 'habis') {
                return $query->where('stok', '<=', 0);
            }
        })
        ->when($sort, function ($query, $sort) {
            if ($sort === 'judul') {
                return $query->orderBy('judul', 'asc');
            } elseif ($sort === 'tahun') {
                return $query->orderBy('tahun_terbit', 'desc');
            } elseif ($sort === 'stok') {
                return $query->orderBy('stok', 'desc');
            }
            return $query->latest();
        }, function ($query) {
            return $query->latest();
        })
        ->paginate(5)
        ->withQueryString();

        // Variabel yang dibutuhkan oleh resources/views/buku/index.blade.php baris 27
        $listPenerbit = Buku::select('penerbit')->whereNotNull('penerbit')->distinct()->pluck('penerbit');
        $penerbitList = $listPenerbit;

        return view('buku.index', compact('buku', 'search', 'penerbit', 'status', 'sort', 'listPenerbit', 'penerbitList'));
    }

    public function create()
    {
        return view('buku.create');
    }

    // TODO 4: Validasi berkas sampul (max: 2048 KB, image) & simpan ke folder 'buku' di storage publik
   // Di dalam BukuController.php -> method store()
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'        => 'required|string|max:255',
            'penulis'      => 'required|string|max:255',
            'penerbit'     => 'required|string|max:255',
            'tahun_terbit' => 'required|integer|min:1900|max:' . date('Y'),
            'stok'         => 'required|integer|min:0',
            'cover'        => 'nullable|max:2048',
        ]);

        if ($request->hasFile('cover')) {
            $validated['cover'] = $request->file('cover')->store('buku', 'public');
        }

        Buku::create($validated);

        return redirect()->route('buku.index')->with('success', 'Data buku berhasil ditambahkan!');
    }

    public function show(Buku $buku)
    {
        return view('buku.show', compact('buku'));
    }

    public function edit(Buku $buku)
    {
        return view('buku.edit', compact('buku'));
    }

    // TODO 5: Hapus berkas fisik sampul lama jika ada berkas sampul baru yang diunggah
    public function update(Request $request, Buku $buku)
    {
        $validated = $request->validate([
            'judul'        => 'required|string|max:255',
            'penulis'      => 'required|string|max:255',
            'penerbit'     => 'required|string|max:255',
            'tahun_terbit' => 'required|integer|min:1900|max:' . date('Y'),
            'stok'         => 'required|integer|min:0',
            'cover'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('cover')) {
            if ($buku->cover && Storage::disk('public')->exists($buku->cover)) {
                Storage::disk('public')->delete($buku->cover);
            }
            $validated['cover'] = $request->file('cover')->store('buku', 'public');
        }

        $buku->update($validated);

        return redirect()->route('buku.index')->with('success', 'Data buku berhasil diperbarui!');
    }

    // TODO 6: Hapus fisik berkas sampul dari disk penyimpanan sebelum data buku dihapus
    public function destroy(Buku $buku)
    {
        if ($buku->cover && Storage::disk('public')->exists($buku->cover)) {
            Storage::disk('public')->delete($buku->cover);
        }

        $buku->delete();

        return redirect()->route('buku.index')->with('success', 'Data buku berhasil dihapus!');
    }
}