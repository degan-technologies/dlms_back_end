<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Http\Resources\Reservation\ReservationCollection;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    // List all reservations (with pagination)
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $query = Reservation::query();

        // Search filter (by reservation_code or user name)
        if ($search = $request->input('filter')) {
            $query->where(function ($q) use ($search) {
                $q->where('reservation_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('reservation_code', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Date range filter (reservation_date)
        $dateRange = $request->input('dateRange', []);
        if (count($dateRange) === 2) {
            $start = $dateRange[0];
            $end = $dateRange[1];
            $query->whereBetween('reservation_date', [$start, $end]);
        }

        $reservations = $query->paginate($perPage);

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
        $user = Auth::user();

        // Check if user already has a reservation
        // Check if user already has an active reservation (pending) or an active loan (not returned)
        $hasActiveReservation = Reservation::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        $hasActiveLoan = \App\Models\Loan::where('user_id', $user->id)
            ->whereNull('returned_date')
            ->exists();

        if ($hasActiveReservation || $hasActiveLoan) {
            return response()->json(['error' => 'You already have an active reservation or loan.'], 422);
        }

        $validated = $request->validate([
            'book_item_id' => 'required|integer|exists:book_items,id',
        ]);

        // Find an available book under the requested book_item_id
        $book = Book::where('book_item_id', $validated['book_item_id'])
            ->where('is_reserved', false)
            ->orderByDesc('publication_year')
            ->first();

        if (!$book) {
            return response()->json(['error' => 'No available book found for this book item.'], 422);
        }

        $reservationData = [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'library_id' => $book->library_id,
            'status' => 'pending',
            'reservation_code' => 'RES-' . strtoupper(uniqid()),
            'reservation_date' => now(),
        ];

        $reservation = Reservation::create($reservationData);

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
