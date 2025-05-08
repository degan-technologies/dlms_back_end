<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use Illuminate\Http\Request;

class FineController extends Controller
{
    /**
     * Fetch and return all fines.
     */
    public function index()
    {
        $fines = Fine::all();
        return response()->json($fines, 200);
    }

    /**
     * Create a new fine.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'library_branch_id' => 'required|integer|exists:library_branches,id',
            'user_id'           => 'required|integer|exists:users,id',
            'loan_id'           => 'required|integer|exists:loans,id',
            'fine_amount'       => 'required|numeric',
            'fine_date'         => 'required|date',
            'reason'            => 'nullable|string',
            'payment_date'      => 'nullable|date',
            'payment_status'    => 'required|string|in:Unpaid,Paid',
        ]);

        $fine = Fine::create($validated);

        return response()->json($fine, 201);
    }

    /**
     * Fetch and return a single fine by its ID.
     */
    public function show($id)
    {
        $fine = Fine::findOrFail($id);
        return response()->json($fine, 200);
    }

    /**
     * Update an existing fine.
     */
    public function update(Request $request, $id)
    {
        $fine = Fine::findOrFail($id);

        $validated = $request->validate([
            'library_branch_id' => 'required|integer|exists:library_branches,id',
            'user_id'           => 'required|integer|exists:users,id',
            'loan_id'           => 'required|integer|exists:loans,id',
            'fine_amount'       => 'required|numeric',
            'fine_date'         => 'required|date',
            'reason'            => 'nullable|string',
            'payment_date'      => 'nullable|date',
            'payment_status'    => 'required|string|in:Unpaid,Paid',
        ]);

        $fine->update($validated);

        return response()->json($fine, 200);
    }

    /**
     * Soft‑delete a fine.
     */
    public function destroy($id)
    {
        $fine = Fine::findOrFail($id);
        $fine->delete();

        return response()->json(['message' => 'Fine deleted successfully'], 200);
    }
}
