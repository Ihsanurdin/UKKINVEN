<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        
        $query = DB::table('barang')
                    ->select('barang.id', 'barang.merk', 'barang.seri', 'barang.spesifikasi', 'barang.stok', 'barang.kategori_id', 'kategori.deskripsi');
    
        $query->leftJoin('kategori', 'barang.kategori_id', '=', 'kategori.id');
    
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('barang.merk', 'like', '%' . $search . '%')
                  ->orWhere('barang.seri', 'like', '%' . $search . '%')
                  ->orWhere('barang.spesifikasi', 'like', '%' . $search . '%')
                  ->orWhere('kategori.deskripsi', 'like', '%' . $search . '%'); // Search in category name
            });
        }
    
        $rsetBarang = $query->paginate(5);
        Paginator::useBootstrap();
        
        return view('barang.index', compact('rsetBarang'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategori = Kategori::all();

        $aKategori = [
            'blank' => 'Pilih Kategori',
            'M' => 'Barang Modal',
            'A' => 'Alat',
            'BHP' => 'Bahan Habis Pakai',
            'BTHP' => 'Bahan Tidak Habis Pakai'
        ];

        return view('barang.create', compact('aKategori', 'kategori'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'merk' => 'required',
            'seri' => 'required|unique:barang',
            'spesifikasi' => 'required',
            'kategori_id' => 'required',
            'stok' => 'nullable|integer|min:0',
        ], [
            'merk.required' => 'Merk harus diisi.',
            'seri.required' => 'Seri harus diisi.',
            'seri.unique' => 'Seri sudah ada, gunakan merk lain.',
            'spesifikasi.required' => 'Spesifikasi harus diisi.',
            'kategori_id.required' => 'Kategori harus dipilih.',
            'stok.integer' => 'Stok harus berupa angka.',
            'stok.min' => 'Stok minimal adalah 0.',
        ]);
    
        try {
            Barang::create([
                'merk' => $request->merk,
                'seri' => $request->seri,
                'spesifikasi' => $request->spesifikasi,
                'stok' => $request->stok ?? 0,
                'kategori_id' => $request->kategori_id,
            ]);
    
            return redirect()->route('barang.index')->with('success', 'Data Berhasil Disimpan!');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan saat menyimpan data.'])->withInput();
        }
    }
    
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $rsetBarang = Barang::findOrFail($id);
        $deskripsiKategori = Kategori::findOrFail($rsetBarang->kategori_id)->deskripsi;
        return view('barang.show', compact('rsetBarang', 'deskripsiKategori'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $rsetBarang = Barang::findOrFail($id);
        $kategoriID = Kategori::all();
        return view('barang.edit', compact('rsetBarang', 'kategoriID'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'merk' => 'required',
            'seri' => 'required',
            'spesifikasi' => 'required',
            'kategori_id' => 'required',
            'stok' => 'nullable|integer|min:0',
        ], [
            'merk.required' => 'Merk harus diisi.',
            'seri.required' => 'Seri harus diisi.',
            'spesifikasi.required' => 'Spesifikasi harus diisi.',
            'kategori_id.required' => 'Kategori harus dipilih.',
            'stok.integer' => 'Stok harus berupa angka.',
            'stok.min' => 'Stok minimal adalah 0.',
        ]);

        try {
            $barang = Barang::findOrFail($id);
            $barang->update([
                'merk' => $request->merk,
                'seri' => $request->seri,
                'spesifikasi' => $request->spesifikasi,
                'kategori_id' => $request->kategori_id,
                'stok' => $request->stok ?? 0,
            ]);

            return redirect()->route('barang.index')->with('success', 'Data Berhasil Diubah!');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan saat mengubah data.'])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $barang = Barang::findOrFail($id);
            $barang->delete();
            return redirect()->route('barang.index')->with('success', 'Data Berhasil Dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('barang.index')->withErrors(['message' => 'Gagal menghapus data.']);
        }
    }
}
