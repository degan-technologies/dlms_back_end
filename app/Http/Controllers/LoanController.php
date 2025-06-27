<?php

namespace App\Http\Controllers;

use App\Http\Resources\Loan\LoanResource;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    /**
     * Librarian-specific loan index
     */
    public function librarianIndex(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $filter = $request->input('filter', null);
        $status = $request->input('status', null);
        $dateRange = $request->input('dateRange', []);

        $query = Loan::query();

        // Filter by search string (filter)
        if ($filter) {
            $bookIds = \App\Models\Book::where('title', 'like', "%$filter%")->pluck('id');
            $query->whereIn('book_id', $bookIds);
        }

        // Filter by status
        if ($status) {
            if (strtolower($status) === 'returned') {
                $query->whereNotNull('returned_date')->where('returned_date', '!=', '');
            } else {
                $query->where(function ($q) {
                    $q->whereNull('returned_date')->orWhere('returned_date', '');
                });
            }
        }

        // Filter by date range (borrow_date)
        if (is_array($dateRange) && count($dateRange) === 2) {
            $start = $dateRange[0];
            $end = $dateRange[1];
            if ($start && $end) {
                $query->whereBetween('borrow_date', [$start, $end]);
            }
        }

        $loans = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => LoanResource::collection($loans),
            'meta' => [
                'total_records' => $loans->total(),
                'per_page' => $loans->perPage(),
                'current_page' => $loans->currentPage(),
                'total_pages' => $loans->lastPage(),
            ],
        ]);
    }

    /**
     * Admin-specific loan index
     */
    public function adminIndex(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $loans = Loan::paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => LoanResource::collection($loans),
            'meta' => [
                'total_records' => $loans->total(),
                'per_page' => $loans->perPage(),
                'current_page' => $loans->currentPage(),
                'total_pages' => $loans->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'book_item_id' => 'required|integer',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date',
            'return_date' => 'nullable|date',
            'library_id' => 'required|integer',
            'book_id' => 'required|integer',
            'user_id' => 'nullable|integer',
        ]);

        // Set both student_id and user_id to the authenticated user's ID if not provided
        $userId = Auth::id();
        $validatedData['student_id'] = $userId;
        if (empty($validatedData['user_id'])) {
            $validatedData['user_id'] = $userId;
        }

        $loan = Loan::create($validatedData);
        return new LoanResource($loan);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $loan = Loan::findOrFail($id);
        return new LoanResource($loan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'nullable|integer',
            'book_id' => 'required|integer',
            'returned_date' => 'required|date',
            'library_id' => 'nullable|integer',
        ]);

        $loan = Loan::findOrFail($id);
        $loan->update($validatedData);

        // Update book reservation status if returned
        if (!empty($validatedData['returned_date']) && $loan->book_id) {
            $book = \App\Models\Book::find($loan->book_id);
            if ($book) {
                $book->is_reserved = 0;
                $book->save();
            }
        }

        return new LoanResource($loan);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $loan = Loan::findOrFail($id);
        $loan->delete();
        return response()->json(['message' => 'Loan deleted successfully']);
    }
}
