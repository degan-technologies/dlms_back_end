<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Http\Resources\Reservation\ReservationCollection;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    // List all reservations (with pagination)
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 30);
        $reservations = Reservation::paginate($perPage);
        return (new ReservationCollection($reservations))
            ->additional([
                'meta' => [
                    'total' => $reservations->total(),
                    'per_page' => $reservations->perPage(),
                    'current_page' => $reservations->currentPage(),
                    'last_page' => $reservations->lastPage(),
                ]
            ]);
    }

    // Store a new reservation
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'expiration_time' => 'nullable|date',
            'reservation_code' => 'required|string|max:50|unique:reservations,reservation_code',
            'user_id' => 'required|integer|exists:users,id',
            'book_item_id' => 'required|integer|exists:book_items,id',
            'library_id' => 'required|integer|exists:libraries,id',
        ]);

        // Find an available book under the requested book_item_id
        $book = \App\Models\Book::where('book_item_id', $validated['book_item_id'])
            ->where('is_reserved', false)
            ->first();

        if (!$book) {
            return response()->json(['error' => 'No available book found for this book item.'], 422);
        }

        $validated['book_id'] = $book->id;
        unset($validated['book_item_id']); // Remove book_item_id, not needed in reservations table

        $reservation = Reservation::create($validated);

        // Mark the book as reserved
        $book->is_reserved = true;
        $book->save();

        return response()->json($reservation, 201);
    }

    // Show a single reservation
    public function show($id)
    {
        $reservation = Reservation::findOrFail($id);
        return response()->json($reservation);
    }

    // Update a reservation
    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        $validated = $request->validate([
            'reservation_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'expiration_time' => 'nullable|date',
            'reservation_code' => 'sometimes|required|string|max:50|unique:reservations,reservation_code,' . $id,
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'book_id' => 'sometimes|required|integer|exists:books,id',
            'library_id' => 'sometimes|required|integer|exists:libraries,id',
        ]);
        $reservation->update($validated);
        return response()->json($reservation);
    }

    // Delete (soft delete) a reservation
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();
        return response()->json(['message' => 'Reservation deleted successfully']);
    }
}
