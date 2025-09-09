import React, { useState } from 'react';

interface Review {
    id: number;
    user: {
        name: string;
        avatar?: string;
    };
    rating: number;
    title?: string;
    comment: string;
    created_at: string;
    helpful_count?: number;
    verified_purchase?: boolean;
}

interface ReviewsListProps {
    reviews: Review[];
    averageRating: number;
    totalReviews: number;
    onSubmitReview?: (review: { rating: number; title: string; comment: string }) => void;
    canReview?: boolean;
}

export default function ReviewsList({ 
    reviews, 
    averageRating, 
    totalReviews, 
    onSubmitReview,
    canReview = false 
}: ReviewsListProps) {
    const [showReviewForm, setShowReviewForm] = useState(false);
    const [newReview, setNewReview] = useState({
        rating: 5,
        title: '',
        comment: ''
    });

    const renderStars = (rating: number, size: 'sm' | 'md' | 'lg' = 'md') => {
        const sizeClasses = {
            sm: 'w-4 h-4',
            md: 'w-5 h-5',
            lg: 'w-6 h-6'
        };

        return (
            <div className="flex">
                {[1, 2, 3, 4, 5].map((star) => (
                    <svg
                        key={star}
                        className={`${sizeClasses[size]} ${
                            star <= rating ? 'text-yellow-400' : 'text-gray-300'
                        }`}
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                ))}
            </div>
        );
    };

    const handleSubmitReview = (e: React.FormEvent) => {
        e.preventDefault();
        if (onSubmitReview) {
            onSubmitReview(newReview);
            setNewReview({ rating: 5, title: '', comment: '' });
            setShowReviewForm(false);
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    return (
        <div className="space-y-6">
            {/* Reviews Summary */}
            <div className="bg-gray-50 rounded-lg p-6">
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-lg font-semibold">Customer Reviews</h3>
                    {canReview && (
                        <button
                            onClick={() => setShowReviewForm(!showReviewForm)}
                            className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Write a Review
                        </button>
                    )}
                </div>
                
                <div className="flex items-center space-x-4">
                    <div className="flex items-center space-x-2">
                        {renderStars(Math.round(averageRating), 'lg')}
                        <span className="text-2xl font-bold">{averageRating.toFixed(1)}</span>
                    </div>
                    <span className="text-gray-600">
                        Based on {totalReviews} review{totalReviews !== 1 ? 's' : ''}
                    </span>
                </div>
            </div>

            {/* Review Form */}
            {showReviewForm && (
                <form onSubmit={handleSubmitReview} className="bg-white border rounded-lg p-6 space-y-4">
                    <h4 className="text-lg font-semibold">Write a Review</h4>
                    
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Rating
                        </label>
                        <div className="flex space-x-1">
                            {[1, 2, 3, 4, 5].map((star) => (
                                <button
                                    key={star}
                                    type="button"
                                    onClick={() => setNewReview({ ...newReview, rating: star })}
                                    className={`w-8 h-8 ${
                                        star <= newReview.rating ? 'text-yellow-400' : 'text-gray-300'
                                    } hover:text-yellow-400 transition-colors`}
                                >
                                    <svg fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </button>
                            ))}
                        </div>
                    </div>

                    <div>
                        <label htmlFor="review-title" className="block text-sm font-medium text-gray-700 mb-1">
                            Title (optional)
                        </label>
                        <input
                            type="text"
                            id="review-title"
                            value={newReview.title}
                            onChange={(e) => setNewReview({ ...newReview, title: e.target.value })}
                            className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Brief summary of your review"
                        />
                    </div>

                    <div>
                        <label htmlFor="review-comment" className="block text-sm font-medium text-gray-700 mb-1">
                            Review *
                        </label>
                        <textarea
                            id="review-comment"
                            value={newReview.comment}
                            onChange={(e) => setNewReview({ ...newReview, comment: e.target.value })}
                            rows={4}
                            required
                            className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Share your thoughts about this product..."
                        />
                    </div>

                    <div className="flex space-x-3">
                        <button
                            type="submit"
                            className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Submit Review
                        </button>
                        <button
                            type="button"
                            onClick={() => setShowReviewForm(false)}
                            className="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            )}

            {/* Reviews List */}
            <div className="space-y-4">
                {reviews.length === 0 ? (
                    <p className="text-gray-500 text-center py-8">
                        No reviews yet. Be the first to review this product!
                    </p>
                ) : (
                    reviews.map((review) => (
                        <div key={review.id} className="border-b border-gray-200 pb-4">
                            <div className="flex items-start justify-between mb-2">
                                <div className="flex items-center space-x-3">
                                    <div className="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                        {review.user.avatar ? (
                                            <img
                                                src={review.user.avatar}
                                                alt={review.user.name}
                                                className="w-10 h-10 rounded-full object-cover"
                                            />
                                        ) : (
                                            <span className="text-gray-600 font-semibold">
                                                {review.user.name.charAt(0).toUpperCase()}
                                            </span>
                                        )}
                                    </div>
                                    <div>
                                        <p className="font-semibold text-gray-900">{review.user.name}</p>
                                        <div className="flex items-center space-x-2">
                                            {renderStars(review.rating, 'sm')}
                                            {review.verified_purchase && (
                                                <span className="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">
                                                    Verified Purchase
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                </div>
                                <span className="text-sm text-gray-500">
                                    {formatDate(review.created_at)}
                                </span>
                            </div>
                            
                            {review.title && (
                                <h4 className="font-semibold text-gray-900 mb-1">{review.title}</h4>
                            )}
                            
                            <p className="text-gray-700 mb-2">{review.comment}</p>
                            
                            {review.helpful_count && review.helpful_count > 0 && (
                                <p className="text-sm text-gray-500">
                                    {review.helpful_count} people found this helpful
                                </p>
                            )}
                        </div>
                    ))
                )}
            </div>
        </div>
    );
}