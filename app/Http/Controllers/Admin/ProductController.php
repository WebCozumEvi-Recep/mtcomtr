<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function __construct()
    {
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');
    }

    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('products.view'), 403, 'Ürünleri görüntüleme yetkiniz yok.');
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('api_product_id', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.catalog.index', compact('products'));
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermission('products.edit'), 403, 'Yeni ürün ekleme yetkiniz yok.');
        return view('admin.catalog.form', [
            'product' => new Product(),
            'localImageUrl' => null,
        ]);
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermission('products.edit'), 403, 'Yeni ürün ekleme yetkiniz yok.');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku'  => 'nullable|string|max:100|unique:products,sku',
            'api_product_id' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $data['original_image_name'] = $file->getClientOriginalName();
            $filename = 'prod_' . time() . '.' . $file->getClientOriginalExtension();
            
            $uploadPath = public_path('uploads/products');
            if(!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }
            
            $file->move($uploadPath, $filename);
            $data['image_url'] = 'uploads/products/' . $filename;
        }

        Product::create($data);
        return redirect()->route('admin.catalog')->with('success', 'Yeni ürün kataloğa eklendi.');
    }

    public function edit(Product $catalog)
    {
        abort_if(!auth()->user()->hasPermission('products.edit'), 403, 'Ürün düzenleme yetkiniz yok.');
        $product = $catalog; // Route parameter matches 'catalog' name
        $rawImagePath = ltrim((string) $product->getRawOriginal('image_url'), '/');
        $localImageUrl = $rawImagePath !== '' ? url($rawImagePath) : null;

        return view('admin.catalog.form', compact('product', 'localImageUrl'));
    }

    public function update(Request $request, Product $catalog)
    {
        abort_if(!auth()->user()->hasPermission('products.edit'), 403, 'Ürün düzenleme yetkiniz yok.');
        $product = $catalog;
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku'  => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'api_product_id' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $data['original_image_name'] = $file->getClientOriginalName();
            $filename = 'prod_' . time() . '.' . $file->getClientOriginalExtension();
            
            $uploadPath = public_path('uploads/products');
            if(!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0755, true);
            }
            
            $file->move($uploadPath, $filename);
            $data['image_url'] = 'uploads/products/' . $filename;
        }

        $product->update($data);
        return redirect()->route('admin.catalog')->with('success', 'Ürün detayları başarıyla güncellendi.');
    }

    public function destroy(Product $catalog)
    {
        abort_if(!auth()->user()->hasPermission('products.delete'), 403, 'Ürün silme yetkiniz yok.');
        $catalog->delete();
        return redirect()->back()->with('success', 'Ürün sistemden silindi.');
    }
}
