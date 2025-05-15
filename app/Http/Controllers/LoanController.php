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
        $perPage = $request->input('per_page', 10); // Accept `per_page` from the request, default to 10
        $page = $request->input('page', 1); // Accept `page` from the request, default to 1
        $filters = $request->input('filter', null); // Accept `filter` from the request
        $status = $request->input('status', null); // Accept `status` from the request
        $dateRange = $request->input('dateRange', null); // Accept `dateRange` from the request

        $query = Loan::query();

        // Apply filters if provided
        if ($filters) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('bookItem', function ($subQuery) use ($filters) {
                    $subQuery->where('title', 'like', "%$filters%")
                             ->orWhere('author', 'like', "%$filters%");
                })
                ->orWhere('borrow_date', 'like', "%$filters%")
                ->orWhere('due_date', 'like', "%$filters%");
            });
        }

        if ($status) {
            $query->where('return_date', $status === 'Returned' ? '!=' : '=', null);
        }

        if ($dateRange && is_array($dateRange) && count($dateRange) === 2) {
            $query->whereBetween('borrow_date', [$dateRange[0], $dateRange[1]]);
        }

        $loans = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => LoanResource::collection($loans),
            'pagination' => [
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
            'student_id' => 'required|integer',
            'book_item_id' => 'required|integer',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date',
            'return_date' => 'nullable|date',
            'library_branch_id' => 'required|integer',
        ]);

        $loan = Loan::findOrFail($id);
        $loan->update($validatedData);
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