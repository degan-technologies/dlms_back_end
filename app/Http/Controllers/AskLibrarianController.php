<?php

//  // app/Http/Controllers/AskLibrarianController.php
// namespace App\Http\Controllers;

// use App\Models\AskLibrarianMessage;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class AskLibrarianController extends Controller
// {
//     // Student or librarian can view messages for a given student
//     public function index(Request $request)
//     {
//         $user = Auth::user();

//         // Students can only view their own thread
//         if ($user->hasRole('student')) {
//             $studentId = $user->id;
//         } elseif ($user->hasRole('librarian')) {
//             $studentId = $request->query('student_id');
//             if (!$studentId) {
//                 return response()->json(['error' => 'student_id is required'], 400);
//             }
//         } else {
//             return response()->json(['error' => 'Unauthorized'], 403);
//         }

//         $messages = AskLibrarianMessage::where('student_id', $studentId)
//             ->orderBy('created_at')
//             ->with('sender:id,name')
//             ->get();

//         return response()->json($messages);
//     }

//     // Send a message (by student or librarian)
//     public function store(Request $request)
//     {
//         $request->validate([
//             'message' => 'required|string',
//             'student_id' => 'sometimes|exists:users,id', // Required only for librarians
//         ]);

//         $user = Auth::user();
//         $isStudent = $user->hasRole('student');
//         $isLibrarian = $user->hasRole('librarian');

//         if ($isStudent) {
//             $studentId = $user->id;
//         } elseif ($isLibrarian) {
//             if (!$request->student_id) {
//                 return response()->json(['error' => 'student_id is required for librarian replies'], 400);
//             }
//             $studentId = $request->student_id;
//         } else {
//             return response()->json(['error' => 'Unauthorized'], 403);
//         }

//         $message = AskLibrarianMessage::create([
//             'student_id' => $studentId,
//             'sender_id' => $user->id,
//             'sender_role' => $isStudent ? 'student' : 'librarian',
//             'message' => $request->message,
//         ]);

//         return response()->json($message, 201);
//     }
// }



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AskLibrarianMessage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AskLibrarianController extends Controller
{
    /**
     * Fetch all messages for a given session.
     */
    public function index(Request $request)
    {
        // $request->validate([
        //     'session_id' => 'required|string',
        // ]);

        $messages = AskLibrarianMessage::
        // where('session_id', $request->session_id)
            orderBy('created_at')
            ->get();

        return response()->json($messages);
    }

    /**
     * Store a new visitor message (with optional file upload).
     */
    public function store(Request $request)
    {
        $request->validate([
            'message'    => 'nullable|string',
            'name'       => 'nullable|string',
            'email'      => 'nullable|email',
            'session_id' => 'sometimes|string',
            'file'       => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
        ]);

        // Generate a new session ID if none provided
        $sessionId = $request->session_id ?? Str::uuid()->toString();

        // Base payload
        $messageData = [
            'session_id' => $sessionId,
            'name'       => $request->name,    // may be null
            'email'      => $request->email,   // may be null
            'sender'     => 'visitor',
            'message'    => $request->message ?? '',
        ];

        // Handle file upload, if present
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('chat_uploads', 'public');
            // store a publicly-accessible URL
            $messageData['file_url'] = asset('storage/' . $path);
        }

        $message = AskLibrarianMessage::create($messageData);

        return response()->json([
            'message'    => $message,
            'session_id' => $sessionId,
        ], 201);
    }

    /**
     * Store a librarian’s reply on an existing session.
     */
    public function reply(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'message'    => 'required|string',
        ]);

        // We don’t expect a name/email from the librarian side; leave nullable
        $reply = AskLibrarianMessage::create([
            'session_id' => $request->session_id,
            'name'       => null,
            'email'      => null,
            'sender'     => 'librarian',
            'message'    => $request->message,
            // file_url will default to null
        ]);

        return response()->json($reply, 201);
    }
}
