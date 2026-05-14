<?php

namespace App\Http\Controllers;

use App\Models\NikeProduct;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = NikeProduct::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%');
        }

        if ($request->category) {
            $query->where('sub_title', 'like', '%' . $request->category . '%');
        }

        return Inertia::render('Catalog/Index', [
            'products' => $query->orderBy('name')->paginate(12)->withQueryString(),
            'filters' => $request->only(['search', 'category']),
        ]);
    }
}
