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
    public function index()
    {
        $loans = Loan::all();
        return LoanResource::collection($loans);
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
            'library_branch_id' => 'required|integer',
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
