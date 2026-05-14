<?php

namespace App\Http\Controllers;

use App\Models\NikeReview;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = NikeReview::query();

        if ($request->rating) {
            $query->where('rating', $request->rating);
        }

        if ($request->search) {
            $query->where('review', 'like', '%' . $request->search . '%')
                  ->orWhere('product_title', 'like', '%' . $request->search . '%');
        }

        return Inertia::render('Feedback/Index', [
            'reviews' => $query->orderByDesc('review_date')->paginate(10)->withQueryString(),
            'filters' => $request->only(['rating', 'search']),
        ]);
    }
}
