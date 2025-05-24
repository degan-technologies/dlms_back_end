<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use Illuminate\Http\Request;
use App\Http\Resources\Fine\FineResource;
use Illuminate\Support\Facades\Log;

class FineController extends Controller
{
    /**
     * Fetch and return all fines.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Accept `per_page` from the request, default to 10
        $page = $request->input('page', 1); // Accept `page` from the request, default to 1
        $filters = $request->input('filter', null); // Accept `filter` from the request
        $status = $request->input('status', null); // Accept `status` from the request
        $dateRange = $request->input('dateRange', null); // Accept `dateRange` from the request

        $query = Fine::query();

        // Apply filters if provided
        if ($filters) {
            $query->where(function ($q) use ($filters) {
                $q->where('reason', 'like', "%$filters%")
                    ->orWhere('fine_amount', 'like', "%$filters%")
                    ->orWhere('user_id', 'like', "%$filters%")
                    ->orWhere('loan_id', 'like', "%$filters%");
            });
        }

        if ($status) {
            $query->where('payment_status', $status);
        }

        if ($dateRange && is_array($dateRange) && count($dateRange) === 2) {
            $query->whereBetween('fine_date', [$dateRange[0], $dateRange[1]]);
        }

        $fines = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => FineResource::collection($fines),
            'pagination' => [
                'total_records' => $fines->total(),
                'per_page' => $fines->perPage(),
                'current_page' => $fines->currentPage(),
                'total_pages' => $fines->lastPage(),
            ],
        ]);
    }

    /**
     * Create a new fine.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'library_id' => 'required|integer|exists:libraries,id',
            'user_id'           => 'required|integer|exists:users,id',
            'loan_id'           => 'required|integer|exists:loans,id',
            'fine_amount'       => 'required|numeric',
            'fine_date'         => 'required|date',
            'reason'            => 'nullable|string',
            'payment_date'      => 'nullable|date',
            'payment_status'    => 'required|string|in:Unpaid,Paid',
            'receipt_path'      => 'nullable|file|mimes:jpg,jpeg,png,pdf',
        ]);

        // Handle file upload if present
        if ($request->hasFile('receipt_path')) {
            $file = $request->file('receipt_path');
            $path = $file->store('receipts', 'public');
            $validated['receipt_path'] = $path;
        }

        // Convert payment_status to boolean
        if (array_key_exists('payment_status', $validated)) {
            $validated['payment_status'] = $validated['payment_status'] === 'Paid' ? true : false;
        }

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
     * Update only payment_date, payment_status, and receipt_path of a fine.
     */
    public function update(Request $request, $id)
    {
        $debug = [];
        $fine = Fine::findOrFail($id);

        $debug['request_all'] = $request->all();
        $debug['request_files'] = $request->allFiles();
        $debug['hasFile'] = $request->hasFile('receipt_path');

        $validated = $request->validate([
            'payment_date'   => 'nullable|date',
            'payment_status' => 'nullable|string|in:Unpaid,Paid',
            'receipt_path' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
        ]);

        $debug['validated'] = $validated;

        // Always handle file upload and set path if present
        if ($request->hasFile('receipt_path')) {
            $file = $request->file('receipt_path');
            $path = $file->store('receipts', 'public');
            $validated['receipt_path'] = $path;
            $debug['stored_path'] = $path;
        }

        if (array_key_exists('payment_status', $validated)) {
            $validated['payment_status'] = $validated['payment_status'] === 'Paid' ? true : false;
        }

        if (count($validated)) {
            $fine->forceFill($validated)->save();
            $debug['updated_fine'] = $fine->toArray();
        } else {
            $debug['message'] = 'No valid fields to update.';
        }

        // Return debug info alongside resource data
        return response()->json([
            'data' => new FineResource($fine->fresh()),
            'debug' => $debug,
        ]);
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
