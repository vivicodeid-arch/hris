<?php

namespace App\Http\Controllers;

use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;

class ProjectCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = ProjectCategory::orderBy('nama_kategori')->get();
        return view('project.category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('project.category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:project_categories,nama_kategori',
            'warna' => 'nullable|string|max:7',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique' => 'Kategori ini sudah terdaftar.',
        ]);

        try {
            ProjectCategory::create([
                'nama_kategori' => $request->nama_kategori,
                'deskripsi' => $request->deskripsi,
                'warna' => $request->warna ?? '#696cff', // Default primary color
            ]);

            return Redirect::back()->with(messageSuccess('Kategori project berhasil disimpan.'));
        } catch (\Exception $e) {
            return Redirect::back()->withInput()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $category = ProjectCategory::findOrFail($id);
            return view('project.category.edit', compact('category'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError('Kategori tidak ditemukan.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
            $category = ProjectCategory::findOrFail($id);

            $request->validate([
                'nama_kategori' => 'required|string|max:100|unique:project_categories,nama_kategori,' . $id,
                'warna' => 'nullable|string|max:7',
            ], [
                'nama_kategori.required' => 'Nama kategori wajib diisi.',
                'nama_kategori.unique' => 'Kategori ini sudah terdaftar.',
            ]);

            $category->update([
                'nama_kategori' => $request->nama_kategori,
                'deskripsi' => $request->deskripsi,
                'warna' => $request->warna ?? '#696cff',
            ]);

            return Redirect::back()->with(messageSuccess('Kategori project berhasil diupdate.'));
        } catch (\Exception $e) {
            return Redirect::back()->withInput()->with(messageError($e->getMessage()));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $id = Crypt::decrypt($id);
            ProjectCategory::destroy($id);
            return Redirect::back()->with(messageSuccess('Kategori project berhasil dihapus.'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }
}
