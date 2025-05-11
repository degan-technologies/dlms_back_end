<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use Illuminate\Http\Request;
use App\Http\Resources\Fine\FineResource;

class FineController extends Controller
{
    /**
     * Fetch and return all fines.
     */
    public function index()
    {
        $fines = Fine::all();
        return FineResource::collection($fines);
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

        return new FineResource($fine);
    }

    /**
     * Fetch and return a single fine by its ID.
     */
    public function show($id)
    {
        $fine = Fine::findOrFail($id);
        return new FineResource($fine);

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

        return new FineResource($fine);
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
