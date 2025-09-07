<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * Store a new review
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'title' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $product = Product::findOrFail($request->product_id);

        // Check if user has purchased this product
        $hasPurchased = $user->orders()
            ->whereHas('items', function ($query) use ($request) {
                $query->where('product_id', $request->product_id);
            })
            ->where('status', 'completed')
            ->exists();

        if (!$hasPurchased) {
            throw ValidationException::withMessages([
                'product_id' => 'You can only review products you have purchased',
            ]);
        }

        // Check if user already reviewed this product
        $existingReview = Review::where('user_id', $user->getKey())
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingReview) {
            throw ValidationException::withMessages([
                'product_id' => 'You have already reviewed this product',
            ]);
        }

        $review = Review::create([
            'user_id' => $user->getKey(),
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'title' => $request->title,
        ]);

        return response()->json([
            'message' => 'Review added successfully',
            'review' => $review->load('user:id,name'),
        ], 201);
    }
}