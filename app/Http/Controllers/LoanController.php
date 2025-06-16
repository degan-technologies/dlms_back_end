<?php

namespace App\Http\Controllers;

use App\Http\Resources\Loan\LoanResource;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $filter = $request->input('filter', null);
        $status = $request->input('status', null);
        $dateRange = $request->input('dateRange', []);

        $query = Loan::query();

        // Filter by search string (filter)
        if ($filter) {
            // Get book IDs where the title matches the filter
            $bookIds = \App\Models\Book::where('title', 'like', "%$filter%")->pluck('id');
            // Filter loans by those book IDs
            $query->whereIn('book_id', $bookIds);
        }

        // Filter by status
        if ($status) {
            if (strtolower($status) === 'returned') {
            // Only include loans where returned_date is a valid date (not null and not empty)
            $query->whereNotNull('returned_date')->where('returned_date', '!=', '');
            } else {
            // For any other status, include loans where returned_date is null or empty
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
        ]);

        $validatedData['student_id'] = Auth::id();
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

        // Set the related book's is_reserved to 0 (false) if returned
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
