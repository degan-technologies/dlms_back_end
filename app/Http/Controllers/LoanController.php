<?php

namespace App\Http\Controllers;
use App\Http\Resources\Loan\LoanResource;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    
    public function history(Request $request)
    {
        $perPage = $request->input('per_page', 6); // Default to 10 per page
        $page = $request->input('page', 1);
        $user = Auth::user();
        $role = $user->roles[0]->name;

        // Initialize query with relationships
        $query = Loan::with(['user', 'book', 'user.libraryBranch']);

        // Apply role-based filters
        switch($role) {
            case 'superadmin':
                // Superadmin can see all loans across branches
                break;
            
            case 'admin':
                // Admin can only see loans from their library branch
                $query->whereHas('user', function($q) use ($user) {
                    $q->where('library_branch_id', $user->library_branch_id);
                });
                break;
            
            case 'librarian':
                // Librarian can only see loans from their library
                $query->whereHas('user', function($q) use ($user) {
                    $q->where('library_branch_id', $user->library_branch_id);
                });
                break;
            
            default:
                // For other roles, only show their own loans
                $query->where('user_id', $user->id);
                break;
        }

        // Additional user_id filter if provided
        if ($request->has('user_id') && ($role === 'superadmin' || $role === 'admin')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $loans = $query->orderBy('borrow_date', 'desc')->paginate($perPage, ['*'], 'page', $page);

        // Add status to each loan
        $history = $loans->getCollection()->map(function ($loan) {
            return [
                'loan_id'      => $loan->id,
                'user'         => $loan->user,
                'book'         => $loan->book,
                'borrow_date'  => $loan->borrow_date,
                'due_date'     => $loan->due_date,
                'returned_date'=> $loan->returned_date,
                'status'       => $loan->returned_date ? 'returned' : 'not returned',
            ];
        });

        // Replace the collection with the mapped history
        $loans->setCollection($history);

        return response()->json([
            'data' => $loans->items(),
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