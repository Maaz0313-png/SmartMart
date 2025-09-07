import React from 'react';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/react/24/outline';

interface PaginationProps {
    currentPage: number;
    totalPages: number;
    onPageChange: (page: number) => void;
    showPageNumbers?: number;
}

export default function Pagination({
    currentPage,
    totalPages,
    onPageChange,
    showPageNumbers = 5
}: PaginationProps) {
    if (totalPages <= 1) return null;

    const getPageNumbers = () => {
        const pages = [];
        const halfRange = Math.floor(showPageNumbers / 2);
        
        let startPage = Math.max(1, currentPage - halfRange);
        let endPage = Math.min(totalPages, currentPage + halfRange);
        
        // Adjust if we're near the beginning or end
        if (endPage - startPage + 1 < showPageNumbers) {
            if (startPage === 1) {
                endPage = Math.min(totalPages, startPage + showPageNumbers - 1);
            } else {
                startPage = Math.max(1, endPage - showPageNumbers + 1);
            }
        }
        
        // Add first page and ellipsis if needed
        if (startPage > 1) {
            pages.push(1);
            if (startPage > 2) {
                pages.push('...');
            }
        }
        
        // Add visible page numbers
        for (let i = startPage; i <= endPage; i++) {
            pages.push(i);
        }
        
        // Add last page and ellipsis if needed
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                pages.push('...');
            }
            pages.push(totalPages);
        }
        
        return pages;
    };

    const pageNumbers = getPageNumbers();

    return (
        <nav className="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0">
            <div className="flex w-0 flex-1">
                {currentPage > 1 && (
                    <button
                        onClick={() => onPageChange(currentPage - 1)}
                        className="inline-flex items-center border-t-2 border-transparent pr-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                    >
                        <ChevronLeftIcon className="mr-3 h-5 w-5 text-gray-400" aria-hidden="true" />
                        Previous
                    </button>
                )}
            </div>
            
            <div className="hidden md:flex">
                {pageNumbers.map((page, index) => {
                    if (page === '...') {
                        return (
                            <span
                                key={`ellipsis-${index}`}
                                className="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500"
                            >
                                ...
                            </span>
                        );
                    }
                    
                    const isCurrentPage = page === currentPage;
                    
                    return (
                        <button
                            key={page}
                            onClick={() => onPageChange(page as number)}
                            className={`inline-flex items-center border-t-2 px-4 pt-4 text-sm font-medium ${
                                isCurrentPage
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'
                            }`}
                        >
                            {page}
                        </button>
                    );
                })}
            </div>
            
            <div className="flex w-0 flex-1 justify-end">
                {currentPage < totalPages && (
                    <button
                        onClick={() => onPageChange(currentPage + 1)}
                        className="inline-flex items-center border-t-2 border-transparent pl-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                    >
                        Next
                        <ChevronRightIcon className="ml-3 h-5 w-5 text-gray-400" aria-hidden="true" />
                    </button>
                )}
            </div>
        </nav>
    );
}