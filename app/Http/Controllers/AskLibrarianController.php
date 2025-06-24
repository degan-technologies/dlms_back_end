<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AskLibrarianMessage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AskLibrarianController extends Controller
{
    /**
     * Fetch all messages with their replies
     */
    public function index(Request $request)
    {
        $messages = AskLibrarianMessage::with('reply')
            ->orderBy('created_at')
            ->get();

        return response()->json($messages);
    }

    /**
     * Store a new visitor message (with optional file upload)
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
            'name'       => $request->name,
            'email'      => $request->email,
            'sender'     => 'visitor',
            'message'    => $request->message ?? '',
        ];

        // Handle file upload
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('chat_uploads', 'public');
            $messageData['file_url'] = asset('storage/' . $path);
        }

        $message = AskLibrarianMessage::create($messageData);

        return response()->json([
            'message'    => $message,
            'session_id' => $sessionId,
        ], 201);
    }

    /**
     * Store or update a librarian's reply
     */
    public function reply(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'message'    => 'required|string',
            'parent_id'  => 'required|exists:ask_librarian_messages,id',
        ]);

        // Check if reply already exists for this question
        $existingReply = AskLibrarianMessage::where('parent_id', $request->parent_id)->first();

        if ($existingReply) {
            // Update existing reply
            $existingReply->update(['message' => $request->message]);
            return response()->json($existingReply, 200);
        }

        // Create new reply
        $reply = AskLibrarianMessage::create([
            'session_id' => $request->session_id,
            'parent_id'  => $request->parent_id,
            'sender'     => 'librarian',
            'message'    => $request->message,
        ]);

        return response()->json($reply, 201);
    }

    /**
     * Get messages for a specific session
     */
    public function getSessionMessages($sessionId)
    {
        $messages = AskLibrarianMessage::with('reply')
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        return response()->json($messages);
    }

    public function update(Request $request, $id)
    {
        $message = AskLibrarianMessage::findOrFail($id);

        $request->validate([
            'message' => 'required|string'
        ]);

        $message->update(['message' => $request->message]);

        return response()->json($message);
    }
    // Add these methods to your AskLibrarianController

    /**
     * Delete a message (question or answer)
     */
    public function destroy($id)
    {
        $message = AskLibrarianMessage::findOrFail($id);

        // If deleting a question, also delete its answer
        if ($message->sender === 'visitor') {
            AskLibrarianMessage::where('parent_id', $id)->delete();
        }

        $message->delete();

        return response()->json(['success' => true]);
    }
}
