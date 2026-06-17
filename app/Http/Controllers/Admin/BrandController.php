<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermission('brands.view'), 403, 'Markaları görüntüleme yetkiniz yok.');
        $brands = Brand::withCount('domains')->orderBy('name')->get();
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermission('brands.edit'), 403, 'Yeni marka ekleme yetkiniz yok.');
        return view('admin.brands.form', ['brand' => new Brand]);
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('brands.edit'), 403, 'Yeni marka ekleme yetkiniz yok.');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        Brand::create($data);

        return redirect()->route('admin.brands.index')->with('success', 'Marka başarıyla oluşturuldu.');
    }

    public function edit(Brand $brand)
    {
        abort_if(!auth()->user()->hasPermission('brands.edit'), 403, 'Marka düzenleme yetkiniz yok.');
        return view('admin.brands.form', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        abort_if(!auth()->user()->hasPermission('brands.edit'), 403, 'Marka düzenleme yetkiniz yok.');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $brand->update($data);

        return redirect()->route('admin.brands.index')->with('success', 'Marka başarıyla güncellendi.');
    }

    public function destroy(Brand $brand)
    {
        abort_if(!auth()->user()->hasPermission('brands.delete'), 403, 'Marka silme yetkiniz yok.');
        if ($brand->domains()->count() > 0) {
            return back()->with('error', 'Bu markaya bağlı domainler olduğu için silinemez.');
        }

        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Marka silindi.');
    }
}
