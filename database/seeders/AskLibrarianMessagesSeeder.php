<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\AskLibrarianMessage;

class AskLibrarianMessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Generate a couple of session UUIDs
        $sessions = [
            Str::uuid()->toString(),
            Str::uuid()->toString(),
        ];

        // Conversation 1: visitor and librarian back-and-forth
        AskLibrarianMessage::create([
            'session_id' => $sessions[0],
            'name'       => 'Alice Johnson',
            'email'      => 'alice.johnson@example.com',
            'sender'     => 'visitor',
            'message'    => 'Hello! Do you have the new science fiction book by John Doe?',
            'file_url'   => null,
        ]);

        AskLibrarianMessage::create([
            'session_id' => $sessions[0],
            'name'       => null,
            'email'      => null,
            'sender'     => 'librarian',
            'message'    => 'Hi Alice, yes we do! It arrived yesterday and is located in aisle 3.',
            'file_url'   => null,
        ]);

        // Conversation 2: visitor-only
        AskLibrarianMessage::create([
            'session_id' => $sessions[1],
            'name'       => 'Bob Smith',
            'email'      => 'bob.smith@example.com',
            'sender'     => 'visitor',
            'message'    => 'Can I request an interlibrary loan for Calculus textbooks?',
            'file_url'   => null,
        ]);

        // Conversation 3: visitor uploading a file
        $sessionWithFile = Str::uuid()->toString();
        AskLibrarianMessage::create([
            'session_id' => $sessionWithFile,
            'name'       => 'Carol Lee',
            'email'      => 'carol.lee@example.com',
            'sender'     => 'visitor',
            'message'    => 'Please find attached the form for my request.',
            'file_url'   => asset('storage/chat_uploads/sample_form.pdf'),
        ]);
    }
}
